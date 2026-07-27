import React from 'react';
export function Select({label,options=[],value,onChange}) {
return React.createElement('label',{style:{display:'flex',flexDirection:'column',gap:6,fontFamily:'var(--font-body)'}},
label&&React.createElement('span',{style:{fontSize:11,letterSpacing:'var(--tracking-wide)',textTransform:'uppercase',color:'var(--text-muted)'}},label),
React.createElement('select',{
value,onChange,
style:{border:'none',borderBottom:'1px solid var(--border-strong)',background:'transparent',padding:'10px 2px',fontSize:'var(--text-body)',color:'var(--text-primary)',outline:'none',fontFamily:'var(--font-body)'}
},options.map(o=>React.createElement('option',{key:o,value:o},o)))
);
}
