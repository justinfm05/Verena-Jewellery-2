import React from 'react';
const base={fontFamily:'var(--font-body)',fontSize:'var(--text-body-s)',letterSpacing:'var(--tracking-normal)',border:'none',cursor:'pointer',display:'inline-flex',alignItems:'center',justifyContent:'center',gap:8,transition:'transform var(--duration-fast) var(--ease-standard),background var(--duration-fast) var(--ease-standard),opacity var(--duration-fast) var(--ease-standard)'};
const sizes={s:{padding:'8px 16px',fontSize:'12px'},m:{padding:'12px 24px',fontSize:'13px'},l:{padding:'16px 32px',fontSize:'14px'}};
const variants={
primary:{background:'var(--green-700)',color:'var(--gold-300)',borderRadius:'var(--radius-pill)'},
secondary:{background:'transparent',color:'var(--text-primary)',border:'1px solid var(--border-strong)',borderRadius:'var(--radius-pill)'},
ghost:{background:'transparent',color:'var(--text-primary)',borderRadius:'var(--radius-pill)'},
accent:{background:'var(--accent)',color:'var(--green-900)',borderRadius:'var(--radius-pill)'}
};
export function Button({variant='primary',size='m',disabled=false,children,onClick,style}) {
const v=variants[variant]||variants.primary;
const s=sizes[size]||sizes.m;
const [hover,setHover]=React.useState(false);
const [active,setActive]=React.useState(false);
let bg=v.background;
if(!disabled&&hover){
if(variant==='primary')bg='var(--green-600)';
if(variant==='accent')bg='var(--accent-hover)';
if(variant==='secondary'||variant==='ghost')bg='var(--neutral-100)';
}
return React.createElement('button',{
style:{...base,...s,...v,background:bg,textTransform:'uppercase',fontWeight:500,opacity:disabled?0.4:1,cursor:disabled?'not-allowed':'pointer',transform:active&&!disabled?'scale(0.98)':'scale(1)',...style},
disabled,onClick,
onMouseEnter:()=>setHover(true),onMouseLeave:()=>{setHover(false);setActive(false)},
onMouseDown:()=>setActive(true),onMouseUp:()=>setActive(false)
},children);
}
