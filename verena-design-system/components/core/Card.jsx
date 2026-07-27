import React from 'react';
export function Card({children,style}) {
return React.createElement('div',{style:{background:'var(--surface-card)',borderRadius:'var(--radius-m)',boxShadow:'var(--shadow-card)',padding:'var(--space-5)',...style}},children);
}
