(() => {
    'use strict';
    const config = document.getElementById('workEditorConfig');
    if (!config) return;
    const c = JSON.parse(config.textContent), l = c.labels;
    const $ = id => document.getElementById(id);
    const esc = value => String(value ?? '').replace(/[&<>"']/g, x => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[x]));
    const icon = (action, label, glyph, index) => `<button type="button" class="btn btn-sm btn-outline-secondary" data-action="${action}" data-index="${index}" title="${esc(label)}" aria-label="${esc(label)}">${action === 'view' ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>' : glyph}</button>`;
    const definitions = [
        ['work_title','title','text',true], ['work_content','content','textarea',true], ['work_url','url','url'],
        ['media_length','duration','text'], ['author','author','text'], ['editor','editor','text'],
        ['is_public','access','select',true,[{id:1,label:l.public},{id:0,label:l.paid}]],
        ['consultation_price','price','number'], ['currency_id','currency','select',false,c.options.currencies],
        ['type_id','type','select',true,c.options.types], ['categories_ids','categories','multiple',true,c.options.categories],
        ['status_id','status','select',true,Object.entries(c.statuses).map(([id,item])=>({id,label:item.label}))],
        ['user_id','publisher','select',false,c.options.publishers], ['organization_id','organization','select',false,c.options.organizations]
    ];
    let draft = [], existing = c.files, step = 1, append = false, editContext = null, busy = false;
    const urls = new Set();
    const objectUrl = file => {const url = URL.createObjectURL(file); urls.add(url); return url;};
    const release = url => {if (urls.delete(url)) URL.revokeObjectURL(url);};
    const error = (id, value) => {$(id).textContent = value || ''; $(id).classList.toggle('d-none', !value);};
    async function request(url, data, method = 'POST') {
        const response = await fetch(url, {method, headers:{'X-CSRF-TOKEN':c.csrf,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body:data});
        let payload; try {payload = await response.json();} catch (_) {throw new Error(l.error);}
        if (!response.ok || payload.success === false) throw new Error(payload.errors ? Object.values(payload.errors).flat().join('\n') : (payload.message || l.error));
        return payload;
    }
    function field(def, values) {
        const [name,key,type,required,options] = def, value = values[name] ?? '';
        const attrs = `name="${name}${type === 'multiple' ? '[]' : ''}" ${required ? 'required' : ''}`;
        let control;
        if (type === 'textarea') control = `<textarea class="form-control" ${attrs} rows="4">${esc(value)}</textarea>`;
        else if (type === 'select' || type === 'multiple') {
            const list = [...(options || [])];
            if (value && type !== 'multiple' && !list.some(o => String(o.id) === String(value))) list.push({id:value,label:'#'+value});
            control = `<select class="form-select" ${attrs} ${type === 'multiple' ? 'multiple size="4"' : ''}>${type !== 'multiple' ? `<option value="">${esc(l.choose)}</option>` : ''}${list.map(o => `<option value="${esc(o.id)}" ${(type === 'multiple' ? (value || []).map(String).includes(String(o.id)) : String(value) === String(o.id)) ? 'selected' : ''}>${esc(o.label)}</option>`).join('')}</select>`;
        } else control = `<input class="form-control" type="${type}" ${attrs} value="${esc(value)}" ${type === 'number' ? 'min="0.01" step="0.01"' : ''} ${name === 'media_length' ? 'pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" placeholder="00:00:00"' : ''}>`;
        return `<div class="col-12 ${type === 'textarea' ? '' : 'col-md-6'}"><label class="form-label">${esc(l[key])}${control}</label></div>`;
    }
    function collect(form) {
        const values = Object.fromEntries(new FormData(form));
        values.categories_ids = new FormData(form).getAll('categories_ids[]');
        return values;
    }
    function formData(values) {
        const body = new FormData();
        Object.entries(values).forEach(([key,value]) => {if (Array.isArray(value)) value.forEach(v=>body.append(`${key}[]`,v)); else body.append(key,value ?? '');});
        return body;
    }
    function pricing(container) {
        const free = container.querySelector('[name="is_public"]');
        if (!free) return;
        const paid = String(free.value) === '0';
        ['consultation_price','currency_id'].forEach(name=>{const input = container.querySelector(`[name="${name}"]`); if(input){input.required = paid;input.closest('.col-12').hidden = !paid;}});
    }
    function kind(file) {
        if (file.mime?.startsWith('audio/')) return 'audio';
        if (file.mime?.startsWith('video/')) return 'video';
        if (file.mime?.startsWith('image/')) return 'image';
        const ext = (file.url || file.name || '').split('?')[0].split('.').pop().toLowerCase();
        if (['mp3','wav','flac','ogg','m4a'].includes(ext)) return 'audio';
        if (['mp4','avi','mov','mkv','webm'].includes(ext)) return 'video';
        if (['jpg','jpeg','png','gif','webp'].includes(ext)) return 'image';
        return ext === 'pdf' || file.mime === 'application/pdf' ? 'pdf' : 'document';
    }
    function safeUrl(url) {
        try {const parsed = new URL(url, location.href);return ['http:','https:','blob:'].includes(parsed.protocol) ? parsed.href : '';} catch (_) {return '';}
    }
    function thumbnail(file) {
        const k = kind(file), cover = esc(safeUrl(file.cover || c.fallback)), url = esc(safeUrl(file.url));
        if (k === 'audio') return `<img class="work-cover" src="${cover}" alt="${esc(l.cover)}"><div class="work-audio"><span aria-hidden="true">♫</span><strong>${esc(l.audio)}</strong></div><audio controls preload="none" src="${url}"></audio>`;
        let preview = '';
        if (k === 'image') preview = `<img class="work-cover mt-2" src="${url}" alt="${esc(file.title || file.name)}" loading="lazy">`;
        if (k === 'pdf') preview = `<object class="work-cover mt-2" data="${url}#page=1&toolbar=0" type="application/pdf"><span>PDF</span></object>`;
        if (k === 'video') preview = `<video class="work-cover mt-2" src="${url}" preload="metadata" muted></video>`;
        return `<img class="work-cover" src="${cover}" alt="${esc(l.cover)}" loading="lazy">${preview}`;
    }
    function renderFiles(target, files, editable, persisted = false) {
        const groups = new Map();
        files.forEach((file,index) => {
            const k = kind(file), family = ['image','video'].includes(k) ? 'image' : k === 'audio' ? 'audio' : 'document';
            const key = file.typeId ?? family, label = file.typeLabel || c.types.find(t => t.fr.toLowerCase().includes(family === 'image' ? 'image' : family))?.label || l[family];
            if (!groups.has(key)) groups.set(key,{label,items:[]});
            groups.get(key).items.push({file,index});
        });
        $(target).innerHTML = files.length ? [...groups.values()].map(group=>`<h6>${esc(group.label)}</h6><div class="work-file-grid">${group.items.map(({file,index})=>`<article class="work-file-card">${thumbnail(file)}<div class="work-file-title mt-2">${esc(file.title || '')}${editable ? icon('edit',l.edit,'✎',index) : ''}</div><small class="text-muted d-block">${esc(file.name)}</small><div class="work-file-actions">${icon('view',l.preview,'◉',index)}${editable ? icon('up',l.up,'↑',index)+icon('down',l.down,'↓',index)+icon('remove',l.remove,'×',index) : ''}</div></article>`).join('')}</div>`).join('') : `<p class="text-muted">${esc(l.empty)}</p>`;
        $(target).querySelectorAll('[data-action]').forEach(button => button.addEventListener('click', async () => {
            const index = Number(button.dataset.index), action = button.dataset.action, file = files[index];
            if (action === 'view') return view(file);
            if (action === 'edit') return editFile(file, persisted);
            if (busy) return;
            try {
                busy = true;
                if (action === 'remove') {
                    if (persisted && !confirm(l.confirm_delete)) return;
                    if (persisted) await request(`${c.base}/${c.id}/files/${file.id}`,undefined,'DELETE');
                    else {release(file.url);release(file.cover);}
                    files.splice(index,1);
                } else {
                    const to = index + (action === 'up' ? -1 : 1);
                    if (to < 0 || to >= files.length) return;
                    const reordered = [...files]; [reordered[index],reordered[to]] = [reordered[to],reordered[index]];
                    if (persisted) await request(`${c.base}/${c.id}/files/order`,formData({ids:reordered.map(f=>f.id)}));
                    files.splice(0,files.length,...reordered);
                }
                renderFiles(target,files,editable,persisted);
            } catch (e) {error(persisted ? 'workPageError' : 'workWizardError',e.message);} finally {busy = false;}
        }));
    }
    function view(file) {
        const url = esc(safeUrl(file.url)), k = kind(file);
        $('workPlayer').innerHTML = k === 'image' ? `<img class="work-preview-image" src="${url}" alt="${esc(file.title || file.name)}">` : k === 'video' ? `<video class="work-player" controls autoplay src="${url}"></video>` : k === 'audio' ? `<audio class="work-player" controls autoplay src="${url}"></audio>` : k === 'pdf' ? `<iframe class="work-pdf" title="${esc(file.title || file.name)}" src="${url}"></iframe>` : '';
        const link = document.createElement('a');link.href = safeUrl(file.url);link.target='_blank';link.rel='noopener';link.textContent=l.open;$('workPlayer').append(link);
        $('workViewer').showModal();
    }
    function editFile(file,persisted) {
        editContext = {file,persisted};
        $('workEditFields').innerHTML = `<div class="col-12"><label class="form-label">${esc(l.part_title)}<input class="form-control" name="part_title" maxlength="1000" value="${esc(file.title)}"></label></div><div class="col-12"><label class="form-label">${esc(l.file_name)}<input class="form-control" name="file_name" required maxlength="1000" value="${esc(file.name)}"></label></div>${persisted ? `<div class="col-12"><label class="form-label">${esc(l.media_length)}<input class="form-control" type="number" min="0" step="1" name="media_length" value="${esc(file.duration)}"></label></div>` : ''}<div class="col-12"><label class="form-label">${esc(l.cover)}<input class="form-control" name="cover" type="file" accept="image/jpeg,image/png,image/webp"></label></div>`;
        error('workEditError','');$('workEdit').showModal();
    }
    function displayValue(def,values) {
        const [name,,type,,options] = def, value = values[name];
        if (type === 'multiple') return (value || []).map(id=>(options || []).find(o=>String(o.id)===String(id))?.label || id).join(', ');
        return type === 'select' ? ((options || []).find(o=>String(o.id)===String(value))?.label || '') : value;
    }
    function renderDetails() {
        if (!c.id) return;
        $('workDetails').innerHTML = definitions.map((def,index)=>`<div class="col-md-6 col-lg-4"><div class="work-data">${icon('field',l.edit,'✎',index)}<small class="text-muted">${esc(l[def[1]])}</small><div class="work-data-value">${esc(displayValue(def,c.values)) || '—'}</div></div></div>`).join('');
        $('workDetails').querySelectorAll('button').forEach(button=>button.addEventListener('click',()=>{
            const def = definitions[Number(button.dataset.index)];
            const chosen = ['is_public','consultation_price','currency_id'].includes(def[0]) ? definitions.filter(d=>['is_public','consultation_price','currency_id'].includes(d[0])) : [def];
            editContext = {fields:chosen};$('workEditFields').innerHTML=chosen.map(d=>field(d,c.values)).join('');pricing($('workEditFields'));error('workEditError','');$('workEdit').showModal();
        }));
    }
    $('workEditForm').addEventListener('change',()=>pricing($('workEditFields')));
    $('workEditForm').addEventListener('submit',async event=>{
        event.preventDefault();const form=event.currentTarget, submit=form.querySelector('[type="submit"]');submit.disabled=true;error('workEditError','');
        try {
            if (editContext.fields) {
                const updated={...c.values};
                editContext.fields.forEach(([name,,,])=>{updated[name]=name==='categories_ids' ? new FormData(form).getAll('categories_ids[]') : new FormData(form).get(name);});
                await request(`${c.base}/${c.id}`,formData(updated));c.values=updated;renderDetails();
            } else {
                const {file,persisted}=editContext, data=new FormData(form), cover=data.get('cover');
                if (cover?.size && (!['image/jpeg','image/png','image/webp'].includes(cover.type) || cover.size>10485760)) throw new Error(l.invalid_cover);
                if (persisted) {await request(`${c.base}/${c.id}/files/${file.id}`,data);location.reload();return;}
                file.title=data.get('part_title');file.name=data.get('file_name');
                if (cover?.size) {release(file.cover);file.coverFile=cover;file.cover=objectUrl(cover);}
                renderFiles('workDraftFiles',draft,true);
            }
            $('workEdit').close();
        } catch(e){error('workEditError',e.message);}finally{submit.disabled=false;}
    });
    function addFiles(input) {
        error('workWizardError','');
        for (const file of input.files) {
            if (draft.length >= 50 || file.size>512000*1024 || !/\.(jpg|jpeg|png|gif|webp|mp4|avi|mov|mkv|webm|pdf|doc|docx|txt|mp3|wav|flac|ogg|m4a)$/i.test(file.name)) {error('workWizardError',`${l.invalid_file} ${file.name}`);continue;}
            draft.push({upload:file,name:file.name,url:objectUrl(file),mime:file.type,title:'',cover:null});
        }
        input.value='';$('workInitialCount').textContent=String(draft.length);renderFiles('workDraftFiles',draft,true);
    }
    $('workInitialFiles').addEventListener('change',event=>addFiles(event.target));$('workMoreFiles').addEventListener('change',event=>addFiles(event.target));
    function showStep(value) {
        step=value;document.querySelectorAll('#workWizard [data-step]').forEach(el=>el.hidden=Number(el.dataset.step)!==step);
        document.querySelectorAll('.work-steps li').forEach((el,index)=>el.classList.toggle('active',index+1===step));
        $('workPrevious').hidden=step===(append?2:1);$('workNext').hidden=step===3;$('workPublish').hidden=step!==3;
        if(step===3){
            const values=collect($('workWizardForm'));
            $('workSummary').innerHTML=append?'':definitions.map(def=>`<div class="mb-2"><small class="text-muted">${esc(l[def[1]])}</small><div style="white-space:pre-wrap">${esc(displayValue(def,values))}</div></div>`).join('');
            renderFiles('workPreviewFiles',draft,false);
        }
    }
    function openWizard(onlyFiles) {
        append=onlyFiles;draft.forEach(f=>{release(f.url);release(f.cover);});draft=[];
        $('workWizardForm').reset();$('workCreateFields').innerHTML=onlyFiles?'':definitions.map(d=>field(d,{is_public:1,status_id:c.options.declassified_status_id,categories_ids:[]})).join('');
        pricing($('workCreateFields'));$('workInitialCount').textContent='';error('workWizardError','');renderFiles('workDraftFiles',draft,true);showStep(onlyFiles?2:1);$('workWizard').showModal();
    }
    $('workAdd').addEventListener('click',()=>openWizard(false));$('workAppend')?.addEventListener('click',()=>openWizard(true));
    $('workWizardForm').addEventListener('change',()=>pricing($('workCreateFields')));
    $('workNext').addEventListener('click',()=>{
        error('workWizardError','');
        if(step===1){
            const inputs=[...$('workCreateFields').querySelectorAll('input,textarea,select')];
            const invalid=inputs.find(input=>!input.checkValidity());if(invalid){invalid.reportValidity();return;}
        }
        if (!draft.length){error('workWizardError',l.required_files);return;}
        showStep(step+1);
    });
    $('workPrevious').addEventListener('click',()=>showStep(step-1));
    $('workWizardForm').addEventListener('submit',async event=>{
        event.preventDefault();if(step!==3 || busy)return;busy=true;
        const buttons=[...$('workWizard').querySelectorAll('button')];buttons.forEach(b=>b.disabled=true);$('workPublish').textContent=l.uploading;
        try {
            const data=append?new FormData():formData(collect(event.currentTarget));
            draft.forEach((file,index)=>{data.append(`files_urls[${index}]`,file.upload);data.append(`part_titles[${index}]`,file.title);data.append(`files_names[${index}]`,file.name);if(file.coverFile)data.append(`part_covers[${index}]`,file.coverFile);});
            const result=await request(append?`${c.base}/${c.id}/files`:c.base,data);
            location.href=append?`${c.base}/${c.id}`:`${c.base}/${result.data.id}`;
        }catch(e){error('workWizardError',e.message);}finally{busy=false;buttons.forEach(b=>b.disabled=false);$('workPublish').textContent=l.save;}
    });
    document.querySelectorAll('#workEditorRoot [data-close]').forEach(button=>button.addEventListener('click',()=>$(button.dataset.close).close()));
    $('workWizard').addEventListener('cancel',event=>{if(busy)event.preventDefault();});
    $('workViewer').addEventListener('close',()=>{$('workPlayer').innerHTML='';});
    window.addEventListener('pagehide',()=>urls.forEach(url=>URL.revokeObjectURL(url)));
    renderDetails();if(c.id)renderFiles('workExistingFiles',existing,true,true);
})();
