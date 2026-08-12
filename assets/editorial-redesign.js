(()=>{
 const search=document.querySelector('.reference-search input');
 document.addEventListener('keydown',event=>{
  if(event.key==='/'&&!/input|textarea|select/i.test(document.activeElement?.tagName||'')){
   event.preventDefault();search?.focus();
  }
 });
 document.querySelectorAll('.reference-categories').forEach(rail=>{
  rail.addEventListener('wheel',event=>{if(Math.abs(event.deltaY)>Math.abs(event.deltaX)){event.preventDefault();rail.scrollLeft+=event.deltaY;}},{passive:false});
  rail.querySelector('.active')?.scrollIntoView({inline:'center',block:'nearest'});
 });
})();
