import React from 'react';
export function Badge({children,variant='neutral'}) {
const variants={
neutral:{background:'var(--neutral-100)',color:'var(--text-secondary)'},
accent:{background:'var(--gold-200)',color:'var(--green-800)'},
inverse:{background:'var(--green-700)',color:'var(--gold-300)'}
};
const v=variants[variant]||variants.neutral;
return React.createElement('span',{style:{...v,display:'inline-flex',alignItems:'center',padding:'4px 12px',borderRadius:'var(--radius-pill)',fontFamily:'var(--font-body)',fontSize:11,letterSpacing:'var(--tracking-wide)',textTransform:'uppercase'}},children);
}
