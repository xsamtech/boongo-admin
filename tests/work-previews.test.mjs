import test from 'node:test';
import assert from 'node:assert/strict';
import {createCanvas} from '@napi-rs/canvas';
import {paintDocument} from '../public/assets/js/work-previews.js';

function samplePdf() {
    const objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 100 100] /Resources << >> /Contents 4 0 R >>',
        '<< /Length 27 >>\nstream\n1 0 0 rg 0 0 100 100 re f\n\nendstream',
        `<< /Length 200000 >>\nstream\n${'x'.repeat(200000)}\nendstream`,
    ];
    let content='%PDF-1.4\n';const offsets=[0];
    for(let index=0;index<objects.length;index++) {
        offsets.push(Buffer.byteLength(content));content+=`${index+1} 0 obj\n${objects[index]}\nendobj\n`;
    }
    const xref=Buffer.byteLength(content);
    content+=`xref\n0 ${objects.length+1}\n0000000000 65535 f \n`;
    content+=offsets.slice(1).map(offset=>`${String(offset).padStart(10,'0')} 00000 n \n`).join('');
    content+=`trailer\n<< /Size ${objects.length+1} /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF`;
    return Buffer.from(content);
}

test('PDF first page renders from JSON ranges without fetching the complete document', async () => {
    const pdf=samplePdf(), ranges=[], originalFetch=globalThis.fetch;
    globalThis.fetch=async (url,options) => {
        assert.equal(url,'/preview');
        const {offset,length}=JSON.parse(options.body);ranges.push([offset,length]);
        return {ok:true,json:async()=>({size:pdf.length,data:pdf.subarray(offset,offset+length).toString('base64')})};
    };
    try {
        const canvas=createCanvas(1,1);
        const assets=new URL('../public/assets/addons/pdfjs/',import.meta.url);
        await paintDocument(canvas,{url:'sample.pdf',previewEndpoint:'/preview'},{
            csrf:'test',labels:{preview_unavailable:'Unavailable'},pdfModule:new URL('pdf.mjs',assets).href,
            pdfWorker:new URL('pdf.worker.mjs',assets).href,pdfAssets:assets.href,
        },true);
        assert.equal(canvas.width,300);
        const pixel=canvas.getContext('2d').getImageData(150,150,1,1).data;
        assert.ok(pixel[0]>200 && pixel[1]<40 && pixel[2]<40);
        assert.ok(ranges.length>=2);
        assert.ok(ranges.reduce((total,[,length])=>total+length,0)<pdf.length);
    } finally {globalThis.fetch=originalFetch;}
});
