(()=>{
 document.querySelectorAll('[data-product-slider]').forEach((slider,index)=>{
  const images=[...slider.querySelectorAll('img')];
  if(images.length<2)return;
  let current=0;
  setInterval(()=>{
   images[current].classList.remove('active');
   current=(current+1)%images.length;
   images[current].classList.add('active');
  },2800+(index%3)*350);
 });

 const main=document.getElementById('detailMainImage');
 const mainTitle=document.getElementById('detailMainTitle');
 const mainPosition=document.getElementById('detailMainPosition');
 const mobileMain=document.getElementById('mobileSelectionImage');
 const mobileTitle=document.getElementById('mobileSelectionTitle');
 const mobilePosition=document.getElementById('mobileSelectionPosition');
 const previewChoices=[...document.querySelectorAll('[data-preview-image]')];

 function showPreview(choice){
  if(!choice)return;
  previewChoices.forEach(item=>item.classList.toggle('is-viewing',item===choice));
  if(main){
   main.src=choice.dataset.previewImage;
   main.alt=choice.dataset.previewTitle||'Vista previa de fotografía';
   main.classList.remove('preview-swap');
   void main.offsetWidth;
   main.classList.add('preview-swap');
  }
  if(mainTitle)mainTitle.textContent=choice.dataset.previewTitle||'';
  if(mainPosition)mainPosition.textContent=`FOTO ${choice.dataset.photoIndex||1} DE ${previewChoices.length}`;
  if(mobileMain){mobileMain.src=choice.dataset.previewImage;mobileMain.alt=choice.dataset.previewTitle||'Vista previa de fotografía';}
  if(mobileTitle)mobileTitle.textContent=choice.dataset.previewTitle||'';
  if(mobilePosition)mobilePosition.textContent=`FOTO ${choice.dataset.photoIndex||1} DE ${previewChoices.length}`;
 }

 previewChoices.forEach(choice=>{
  choice.addEventListener('mouseenter',()=>showPreview(choice));
  choice.addEventListener('focusin',()=>showPreview(choice));
  choice.addEventListener('click',()=>showPreview(choice));
  choice.addEventListener('keydown',event=>{
   if(event.key==='Enter'||event.key===' '){
    event.preventDefault();
    const check=choice.querySelector('input[type="checkbox"]');
    if(check){check.checked=!check.checked;check.dispatchEvent(new Event('change',{bubbles:true}));}
    showPreview(choice);
   }
  });
 });

 const initialChoice=previewChoices.find(choice=>choice.classList.contains('is-viewing'))||previewChoices[0];
 if(initialChoice)showPreview(initialChoice);

 const savedKey='astrosport_saved_photos';
 let saved=[];
 try{saved=JSON.parse(localStorage.getItem(savedKey)||'[]').map(String);}catch(error){saved=[];}
 document.querySelectorAll('[data-save-photo]').forEach(button=>{
  const id=String(button.dataset.savePhoto);
  const render=()=>{const active=saved.includes(id);button.classList.toggle('saved',active);button.textContent=active?'✓ Guardada':'＋ Guardar';};
  render();
  button.addEventListener('click',event=>{event.preventDefault();event.stopPropagation();saved=saved.includes(id)?saved.filter(item=>item!==id):[...saved,id];try{localStorage.setItem(savedKey,JSON.stringify(saved));}catch(error){}render();});
 });
 document.querySelectorAll('[data-cart-photo]').forEach(button=>{
  button.addEventListener('click',event=>{
   event.preventDefault();event.stopPropagation();if(button.disabled)return;
   button.disabled=true;
   const gallery=button.closest('[data-photo-gallery]');
   const form=document.createElement('form');
   form.method='post';form.action=gallery?.dataset.cartAddUrl||'';
   const fields={_token:gallery?.dataset.csrf||'',type:'photo',id:button.dataset.photoId||''};
   Object.entries(fields).forEach(([name,value])=>{const input=document.createElement('input');input.type='hidden';input.name=name;input.value=value;form.appendChild(input);});
   document.body.appendChild(form);
   button.classList.add('selected');
   form.requestSubmit();
   setTimeout(()=>{form.remove();button.disabled=false;},900);
  });
 });
 document.querySelectorAll('.editorial-photo-actions a').forEach(link=>link.addEventListener('click',event=>event.stopPropagation()));
})();
