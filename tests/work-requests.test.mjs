import test from 'node:test';
import assert from 'node:assert/strict';
import '../public/assets/js/work-requests.js';

const labels = Object.fromEntries(['network_error','session_expired','access_denied','route_missing','upload_too_large','server_error','server_timeout','unexpected_response','error_reference','error'].map(key=>[key,key]));
function client(response) {
    const logs=[];
    return {logs, options:{labels,csrf:'token',logger:{error:(...args)=>logs.push(args)},fetcher:async()=>response}};
}
test('saves successfully and sends session and CSRF headers',async()=>{
    const options={labels,csrf:'token',fetcher:async(url,options)=>{
        assert.equal(options.credentials,'same-origin');assert.equal(options.headers['X-CSRF-TOKEN'],'token');
        return new Response(JSON.stringify({success:true,data:{id:3}}));
    }};
    assert.equal((await WorkRequests.request('/admin/work',new FormData(),options)).data.id,3);
});
test('HTML payload-too-large response gives an actionable error and console diagnostic',async()=>{
    const {options,logs}=client(new Response('<html>Request Entity Too Large</html>',{status:413,headers:{'Content-Type':'text/html'}}));
    await assert.rejects(WorkRequests.request('/admin/work',null,options),/upload_too_large.*HTTP 413/);
    assert.equal(logs[0][1].status,413);assert.equal(logs[0][1].contentType,'text/html');
});
test('expired session is recognizable for JSON and HTML responses',async()=>{
    for(const status of [401,419]){
        const {options}=client(new Response('<html>Expired</html>',{status}));
        await assert.rejects(WorkRequests.request('/admin/work',null,options),new RegExp(`session_expired.*HTTP ${status}`));
    }
});
test('successful HTML response is not silently treated as a saved work',async()=>{
    const {options,logs}=client(new Response('<html>Login page</html>'));
    await assert.rejects(WorkRequests.request('/admin/work',null,options),/unexpected_response.*HTTP 200/);
    assert.equal(logs.length,1);
});
test('redirect to login is reported even when login returns JSON',async()=>{
    const {options}=client({ok:true,status:200,redirected:true,url:'/login',headers:new Headers(),text:async()=>'{"success":true}'});
    await assert.rejects(WorkRequests.request('/admin/work',null,options),/session_expired/);
});
test('validation messages are preserved',async()=>{
    const {options}=client(new Response(JSON.stringify({errors:{work_title:['Title required'],files_urls:['Too large']}}),{status:422}));
    await assert.rejects(WorkRequests.request('/admin/work',null,options),/Title required\nToo large/);
});
test('server errors include their reference without exposing debug exception messages',async()=>{
    const {options,logs}=client(new Response(JSON.stringify({success:false,message:'Unable to save',reference:'ref-123'}),{status:500}));
    await assert.rejects(WorkRequests.request('/admin/work',null,options),/Unable to save.*HTTP 500.*ref-123/);
    assert.equal(logs[0][1].reference,'ref-123');
    const debug=client(new Response(JSON.stringify({message:'secret SQL exception'}),{status:500}));
    await assert.rejects(WorkRequests.request('/admin/work',null,debug.options),/server_error.*HTTP 500/);
});
test('network errors are logged without logging submitted form data',async()=>{
    const {options,logs}=client(null);
    options.fetcher=async()=>{throw new TypeError('Failed to fetch');};
    await assert.rejects(WorkRequests.request('/admin/work','private form content',options),/network_error/);
    assert.equal(logs[0][1].reason,'network');
    assert.ok(!JSON.stringify(logs).includes('private form content'));
});
