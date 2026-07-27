import React from 'react';
export function Input({label,placeholder,type='text',value,onChange}) {
const [focus,setFocus]=React.useState(false);
return React.createElement('label',{style:{display:'flex',flexDirection:'column',gap:6,fontFamily:'var(--font-body)'}},
label&&React.createElement('span',{style:{fontSize:11,letterSpacing:'var(--tracking-wide)',textTransform:'uppercase',color:'var(--text-muted)'}},label),
React.createElement('input',{
type,placeholder,value,onChange,
onFocus:()=>setFocus(true),onBlur:()=>setFocus(false),
style:{border:'none',borderBottom:`1px solid ${focus?'var(--accent)':'var(--border-strong)'}`,background:'transparent',padding:'10px 2px',fontSize:'var(--text-body)',color:'var(--text-primary)',outline:'none',transition:'border-color var(--duration-fast) var(--ease-standard)'}
})
);
}
