import React from 'react';
export function IconButton({icon,label,variant='ghost',onClick,style}) {
const [hover,setHover]=React.useState(false);
const ref=React.useRef(null);
const dark=variant==='inverse';
React.useEffect(()=>{if(window.lucide)window.lucide.createIcons({nameAttr:'data-lucide',attrs:{width:18,height:18}});},[icon]);
return React.createElement('button',{
'aria-label':label,title:label,onClick,ref,
onMouseEnter:()=>setHover(true),onMouseLeave:()=>setHover(false),
style:{width:40,height:40,borderRadius:'50%',border:dark?'1px solid rgba(242,226,186,0.3)':'1px solid var(--border-subtle)',background:hover?(dark?'rgba(242,226,186,0.12)':'var(--neutral-100)'):'transparent',color:dark?'var(--text-inverse)':'var(--text-primary)',display:'inline-flex',alignItems:'center',justifyContent:'center',cursor:'pointer',transition:'background var(--duration-fast) var(--ease-standard)',...style}
},React.createElement('i',{'data-lucide':icon,style:{width:18,height:18,display:'inline-block'}}));
}
