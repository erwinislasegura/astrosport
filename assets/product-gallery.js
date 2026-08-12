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

 const money=value=>'$'+new Intl.NumberFormat('es-CL').format(value);
 document.querySelectorAll('.smart-photo-selection').forEach(form=>{
  const checks=[...form.querySelectorAll('.smart-selector input[type="checkbox"]')];
  const packs=[...form.querySelectorAll('[data-pack-quantity]')].map(node=>({node,quantity:Number(node.dataset.packQuantity),price:Number(node.dataset.packPrice)}));
  const individual=form.dataset.individualEnabled==='1';
  const count=form.querySelector('.smart-count');
  const total=form.querySelector('.smart-total');
  const mode=form.querySelector('.smart-mode');
  const mobileCount=form.querySelector('.mobile-picker-count');
  const mobileTotal=form.querySelector('.mobile-picker-total');
  const mobileMode=form.querySelector('.mobile-picker-mode');
  const buttons=[...form.querySelectorAll('button[type="submit"]')];

  function setButtons(disabled,label){buttons.forEach(button=>{button.disabled=disabled;button.textContent=label;});}

  function update(){
   const selected=checks.filter(check=>check.checked);
   const pack=packs.find(item=>item.quantity===selected.length);
   count.textContent=selected.length;
   if(mobileCount)mobileCount.textContent=selected.length;
   packs.forEach(item=>item.node.classList.toggle('active',item===pack));
   if(pack){
    total.textContent=money(pack.price);
    if(mobileTotal)mobileTotal.textContent=money(pack.price);
    mode.textContent=`PACK DE ${pack.quantity} ACTIVADO`;
    mode.className='smart-mode pack-active';
    if(mobileMode){mobileMode.textContent=`PACK DE ${pack.quantity} ACTIVADO`;mobileMode.className='mobile-picker-mode pack-active';}
    setButtons(false,`AGREGAR PACK DE ${pack.quantity} FOTOS →`);
   }else if(individual&&selected.length){
    const individualTotal=money(selected.reduce((sum,check)=>sum+Number(check.dataset.price||0),0));
    total.textContent=individualTotal;
    if(mobileTotal)mobileTotal.textContent=individualTotal;
    mode.textContent='VALOR INDIVIDUAL';
    mode.className='smart-mode';
    if(mobileMode){mobileMode.textContent='VALOR INDIVIDUAL';mobileMode.className='mobile-picker-mode';}
    setButtons(false,'AGREGAR SELECCIÓN AL CARRITO →');
   }else{
    total.textContent='$0';
    if(mobileTotal)mobileTotal.textContent='$0';
    mode.textContent=selected.length?'CANTIDAD SIN PACK':'ELIGE TUS FOTOS';
    mode.className='smart-mode';
    if(mobileMode){mobileMode.textContent=selected.length?'CANTIDAD SIN PACK':'ELIGE TUS FOTOS';mobileMode.className='mobile-picker-mode';}
    setButtons(true,'SELECCIONA UNA CANTIDAD DE PACK');
   }
  }

  checks.forEach(check=>check.addEventListener('change',()=>{
   showPreview(check.closest('[data-preview-image]'));
   update();
  }));
  update();
 });

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
  const label=button.closest('.photo-choice');
  const checkbox=label?.querySelector('input[type="checkbox"]');
  const render=()=>button.classList.toggle('selected',Boolean(checkbox?.checked));
  render();
  button.addEventListener('click',event=>{event.preventDefault();event.stopPropagation();if(!checkbox)return;checkbox.checked=!checkbox.checked;checkbox.dispatchEvent(new Event('change',{bubbles:true}));render();});
  checkbox?.addEventListener('change',render);
 });
 document.querySelectorAll('.editorial-photo-actions a').forEach(link=>link.addEventListener('click',event=>event.stopPropagation()));
})();
