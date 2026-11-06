let pdfLibrary;
const snapshots = new Map();

async function jsonRange(file, config, offset = 0, length = 65536) {
    const response = await fetch(file.previewEndpoint, {
        method: 'POST', headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':config.csrf},
        body: JSON.stringify({offset, length}),
    });
    if (!response.ok) throw new Error(config.labels.preview_unavailable);
    return response.json();
}
const bytes = data => Uint8Array.from(atob(data), char => char.charCodeAt(0));

async function localDocx(file) {
    const buffer=await file.arrayBuffer(), view=new DataView(buffer), data=new Uint8Array(buffer);
    let end=view.byteLength-22;
    while(end>=Math.max(0,view.byteLength-65557) && view.getUint32(end,true)!==0x06054b50)end--;
    if(end<0 || view.getUint32(end,true)!==0x06054b50) return '';
    let entry=view.getUint32(end+16,true);
    const count=view.getUint16(end+10,true), decoder=new TextDecoder();
    for(let index=0;index<count;index++) {
        if(view.getUint32(entry,true)!==0x02014b50)break;
        const nameLength=view.getUint16(entry+28,true), extra=view.getUint16(entry+30,true), comment=view.getUint16(entry+32,true);
        const name=decoder.decode(data.subarray(entry+46,entry+46+nameLength));
        if(name==='word/document.xml') {
            if(view.getUint32(entry+24,true)>8*1024*1024)return '';
            const local=view.getUint32(entry+42,true), start=local+30+view.getUint16(local+26,true)+view.getUint16(local+28,true);
            const compressed=data.slice(start,start+view.getUint32(entry+20,true)), method=view.getUint16(entry+10,true);
            const xml=method===0 ? decoder.decode(compressed) : method===8 ? await new Response(new Blob([compressed]).stream().pipeThrough(new DecompressionStream('deflate-raw'))).text() : '';
            return xml.split(/<w:br\b[^>]*w:type="page"[^>]*\/>|<w:lastRenderedPageBreak\s*\/>/)[0]
                .replace(/<\/w:p>/g,'\n').replace(/<[^>]+>/g,'')
                .replace(/&(amp|lt|gt|quot|apos);/g,(_,entity)=>({amp:'&',lt:'<',gt:'>',quot:'"',apos:"'"}[entity])).slice(0,2400);
        }
        entry+=46+nameLength+extra+comment;
    }
    return '';
}

export async function paintDocument(canvas, file, config, isPdf) {
    const key = `${file.url}:${file.previewEndpoint || ''}`;
    if (snapshots.has(key)) {
        const image = new Image(); image.src = snapshots.get(key); await image.decode();
        canvas.width = image.width; canvas.height = image.height;
        canvas.getContext('2d').drawImage(image, 0, 0); return;
    }
    if (!isPdf) {
        let text = '';
        if (file.previewEndpoint) text = (await jsonRange(file, config)).text;
        else if (file.upload?.name.toLowerCase().endsWith('.txt')) text = await file.upload.slice(0,16384).text();
        else if (file.upload?.name.toLowerCase().endsWith('.docx')) text = await localDocx(file.upload);
        if (!text) throw new Error(config.labels.preview_unavailable);
        canvas.width = 300; canvas.height = 390;
        const ctx = canvas.getContext('2d');ctx.fillStyle = '#fff';ctx.fillRect(0,0,300,390);
        ctx.fillStyle='#273142';ctx.font='13px sans-serif';
        let line='', y=25;
        for (const word of text.slice(0,2400).split(/\s+/)) {
            const next=line+word+' ';
            if (ctx.measureText(next).width>260) {ctx.fillText(line,20,y);line=word+' ';y+=19;}
            else line=next;
            if (y>365) break;
        }
        if (y<=365) ctx.fillText(line,20,y);
    } else {
        pdfLibrary ||= import(config.pdfModule);
        const pdfjs = await pdfLibrary;
        pdfjs.GlobalWorkerOptions.workerSrc = config.pdfWorker;
        let task, pdf;
        const options = {
            disableAutoFetch:true, disableStream:true, rangeChunkSize:65536, isEvalSupported:false,
            cMapUrl:config.pdfAssets+'cmaps/', cMapPacked:true,
            standardFontDataUrl:config.pdfAssets+'standard_fonts/', wasmUrl:config.pdfAssets+'wasm/',
        };
        if (file.upload) options.data = new Uint8Array(await file.upload.arrayBuffer());
        else {
            const initial = await jsonRange(file,config);
            const transport = new pdfjs.PDFDataRangeTransport(initial.size, bytes(initial.data), true);
            transport.requestDataRange = (begin,end) => {
                (async () => {
                    for (let offset=begin;offset<end;offset+=65536) {
                        const part=await jsonRange(file,config,offset,Math.min(65536,end-offset));
                        transport.onDataRange(offset,bytes(part.data));
                    }
                })().catch(() => task?.destroy());
            };
            options.range=transport;
        }
        try {
            task=pdfjs.getDocument(options);pdf=await task.promise;
            const page=await pdf.getPage(1), natural=page.getViewport({scale:1});
            const viewport=page.getViewport({scale:300/natural.width});
            canvas.width=Math.ceil(viewport.width);canvas.height=Math.ceil(viewport.height);
            await page.render({canvasContext:canvas.getContext('2d'),viewport}).promise;
        } finally {await task?.destroy();}
    }
    snapshots.set(key,canvas.toDataURL('image/png'));
    if(snapshots.size>60)snapshots.delete(snapshots.keys().next().value);
}
