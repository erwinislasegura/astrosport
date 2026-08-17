(()=>{
 const modal=document.querySelector('[data-photo-lightbox]');
 if(!modal)return;

 const triggers=[...document.querySelectorAll('[data-lightbox-open]')];
 const image=modal.querySelector('[data-lightbox-image]');
 const title=modal.querySelector('[data-lightbox-title]');
 const meta=modal.querySelector('[data-lightbox-meta]');
 const counter=modal.querySelector('[data-lightbox-counter]');
 const previous=modal.querySelector('[data-lightbox-previous]');
 const next=modal.querySelector('[data-lightbox-next]');
 const closeButtons=[...modal.querySelectorAll('[data-lightbox-close]')];
 let current=0;
 let lastFocus=null;
 let touchStartX=0;

 document.querySelectorAll('.editorial-photo-frame img').forEach(preview=>{
  preview.draggable=false;
  preview.addEventListener('contextmenu',event=>event.preventDefault());
  preview.addEventListener('dragstart',event=>event.preventDefault());
 });

 const render=index=>{
  current=(index+triggers.length)%triggers.length;
  const trigger=triggers[current];
  image.src=trigger.dataset.lightboxSrc||'';
  image.alt=trigger.dataset.lightboxTitle||'Vista previa protegida';
  title.textContent=trigger.dataset.lightboxTitle||'Fotografía AstroSport';
  meta.textContent=trigger.dataset.lightboxMeta||'Vista previa protegida';
  counter.textContent=`FOTO ${current+1} DE ${triggers.length}`;
  const single=triggers.length<2;
  previous.disabled=single;
  next.disabled=single;
 };

 const open=index=>{
  lastFocus=document.activeElement;
  render(index);
  modal.hidden=false;
  modal.setAttribute('aria-hidden','false');
  document.body.classList.add('photo-lightbox-open');
  requestAnimationFrame(()=>modal.classList.add('is-open'));
  closeButtons.at(-1)?.focus();
 };

 const close=()=>{
  modal.classList.remove('is-open');
  modal.setAttribute('aria-hidden','true');
  document.body.classList.remove('photo-lightbox-open');
  window.setTimeout(()=>{modal.hidden=true;image.removeAttribute('src');lastFocus?.focus?.();},200);
 };

 triggers.forEach((trigger,index)=>trigger.addEventListener('click',event=>{
  event.preventDefault();
  event.stopPropagation();
  open(index);
 }));
 document.querySelectorAll('.photo-choice').forEach((card,index)=>{
  card.querySelector('.editorial-photo-frame')?.addEventListener('click',event=>{
   if(event.target.closest('.editorial-photo-actions'))return;
   event.preventDefault();
   event.stopPropagation();
   open(index);
  });
  card.addEventListener('keydown',event=>{
   if(event.target===card&&event.key==='Enter'&&!card.querySelector('input[type="checkbox"]')){
    event.preventDefault();
    open(index);
   }
  });
 });
 closeButtons.forEach(button=>button.addEventListener('click',close));
 previous.addEventListener('click',()=>render(current-1));
 next.addEventListener('click',()=>render(current+1));

 modal.addEventListener('keydown',event=>{
  if(event.key==='Escape'){event.preventDefault();close();}
  if(event.key==='ArrowLeft'){event.preventDefault();render(current-1);}
  if(event.key==='ArrowRight'){event.preventDefault();render(current+1);}
  if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='s')event.preventDefault();
  if(event.key==='Tab'){
   const focusable=[...modal.querySelectorAll('button:not(:disabled)')];
   if(!focusable.length)return;
   const first=focusable[0],last=focusable.at(-1);
   if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus();}
   else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus();}
  }
 });

 modal.addEventListener('contextmenu',event=>event.preventDefault());
 modal.addEventListener('dragstart',event=>event.preventDefault());
 modal.addEventListener('touchstart',event=>{touchStartX=event.changedTouches[0]?.clientX||0;},{passive:true});
 modal.addEventListener('touchend',event=>{
  const distance=(event.changedTouches[0]?.clientX||0)-touchStartX;
  if(Math.abs(distance)>55)render(current+(distance<0?1:-1));
 },{passive:true});
})();
