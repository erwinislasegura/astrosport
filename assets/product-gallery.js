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
  const checks=[...form.querySelectorAll('.photo-pack-check')];
  const packs=[...form.querySelectorAll('[data-pack-quantity]')].map(node=>({node,quantity:Number(node.dataset.packQuantity),price:Number(node.dataset.packPrice)}));
  const individual=form.dataset.individualEnabled==='1';
  const count=form.querySelector('.smart-count');
  const total=form.querySelector('.smart-total');
  const mode=form.querySelector('.smart-mode');
  const help=form.querySelector('.smart-help');
  const submit=form.querySelector('.desktop-selection-submit');
  const modeInput=form.querySelector('.smart-purchase-mode');
  const quantityInput=form.querySelector('.smart-pack-quantity');

  function selectedMode(){return modeInput?.value||'individual';}
  function selectedPack(){
   const quantity=Number(quantityInput?.value||0);
   return packs.find(item=>item.quantity===quantity)||null;
  }

  function chooseMode(nextMode,pack=null){
   if(modeInput)modeInput.value=nextMode;
   if(quantityInput)quantityInput.value=pack?String(pack.quantity):'';
   form.querySelectorAll('[data-purchase-mode]').forEach(node=>{
    const active=node.dataset.purchaseMode===nextMode&&(nextMode!=='pack'||node===pack?.node);
    node.classList.toggle('active',active);
    if(node.tagName==='BUTTON')node.setAttribute('aria-pressed',active?'true':'false');
   });
   form.querySelector('.combo-card')?.classList.toggle('active',nextMode==='pack');
   update();
  }

  function update(){
   const selected=checks.filter(check=>check.checked);
   const currentMode=selectedMode();
   const pack=selectedPack();
   if(count)count.textContent=selected.length;
   checks.forEach(check=>{
    const card=check.closest('.photo-choice');
    const toggle=card?.querySelector('[data-select-photo]');
    card?.classList.toggle('is-selected',check.checked);
    if(toggle){toggle.classList.toggle('selected',check.checked);toggle.setAttribute('aria-pressed',check.checked?'true':'false');toggle.textContent=check.checked?'✓ SELECCIONADA':'＋ SELECCIONAR';}
   });
   packs.forEach(item=>item.node.classList.toggle('active',currentMode==='pack'&&item===pack));
   if(currentMode==='set'){
    const setOption=form.querySelector('[data-purchase-mode="set"]');
    const setPrice=Number(setOption?.dataset.setPrice||0);
    if(total)total.textContent=money(setPrice);
    if(mode){mode.textContent='SET COMPLETO';mode.className='smart-mode set-active';}
    if(help)help.textContent='Se agregarán todas las fotografías del set en una sola compra.';
    if(submit){submit.disabled=false;submit.textContent='AGREGAR SET COMPLETO AL CARRITO →';}
   }else if(currentMode==='pack'&&pack){
    if(total)total.textContent=money(pack.price);
    const complete=selected.length===pack.quantity;
    if(mode){mode.textContent=complete?`COMBO DE ${pack.quantity} LISTO`:`FALTAN ${Math.max(0,pack.quantity-selected.length)} FOTOS`;mode.className=complete?'smart-mode pack-active':'smart-mode';}
    if(help)help.textContent=`Selecciona exactamente ${pack.quantity} fotografías para completar este combo.`;
    if(submit){submit.disabled=!complete;submit.textContent=complete?`AGREGAR COMBO DE ${pack.quantity} FOTOS →`:`SELECCIONA ${pack.quantity} FOTOGRAFÍAS`;}
   }else if(currentMode==='individual'&&individual&&selected.length){
    const amount=selected.reduce((sum,check)=>sum+Number(check.dataset.price||0),0);
    if(total)total.textContent=money(amount);
    if(mode){mode.textContent='COMPRA INDIVIDUAL';mode.className='smart-mode';}
    if(help)help.textContent='Cada fotografía seleccionada se agregará como un producto individual.';
    if(submit){submit.disabled=false;submit.textContent=selected.length===1?'AGREGAR FOTO INDIVIDUAL →':`AGREGAR ${selected.length} FOTOS INDIVIDUALES →`;}
   }else{
    if(total)total.textContent='$0';
    if(mode){mode.textContent='ELIGE TUS FOTOS';mode.className='smart-mode';}
    if(submit){submit.disabled=true;submit.textContent='SELECCIONA AL MENOS UNA FOTO';}
   }
  }

  form.querySelectorAll('[data-purchase-mode="individual"]').forEach(button=>button.addEventListener('click',()=>chooseMode('individual')));
  form.querySelectorAll('[data-purchase-mode="set"]').forEach(button=>button.addEventListener('click',()=>chooseMode('set')));
  packs.forEach(pack=>pack.node.addEventListener('click',()=>chooseMode('pack',pack)));
  form.querySelectorAll('[data-select-photo]').forEach(button=>button.addEventListener('click',event=>{
   event.preventDefault();event.stopPropagation();
   const check=button.closest('.photo-choice')?.querySelector('.photo-pack-check');
   if(!check)return;
   check.checked=!check.checked;
   check.dispatchEvent(new Event('change',{bubbles:true}));
  }));
  checks.forEach(check=>check.addEventListener('change',()=>{showPreview(check.closest('[data-preview-image]'));update();}));
  const initialPack=selectedMode()==='pack'?selectedPack():null;
  chooseMode(selectedMode(),initialPack);
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
