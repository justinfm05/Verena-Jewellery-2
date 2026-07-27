import React from 'react';
export function ProductCard({name,price,badge,onAdd}) {
const [hover,setHover]=React.useState(false);
return React.createElement('div',{style:{fontFamily:'var(--font-body)'},onMouseEnter:()=>setHover(true),onMouseLeave:()=>setHover(false)},
React.createElement('div',{style:{position:'relative',aspectRatio:'4/5',background:'var(--neutral-100)',borderRadius:'var(--radius-m)',overflow:'hidden',display:'flex',alignItems:'center',justifyContent:'center',color:'var(--text-muted)',fontSize:12}},
'product photo',
badge&&React.createElement('span',{style:{position:'absolute',top:12,left:12,background:'var(--gold-200)',color:'var(--green-800)',fontSize:10,letterSpacing:'var(--tracking-wide)',textTransform:'uppercase',padding:'4px 10px',borderRadius:'var(--radius-pill)'}},badge),
React.createElement('button',{'aria-label':'Save to wishlist',style:{position:'absolute',top:8,right:8,width:32,height:32,borderRadius:'50%',border:'none',background:hover?'#fff':'transparent',opacity:hover?1:0,transition:'opacity var(--duration-fast) var(--ease-standard),background var(--duration-fast) var(--ease-standard)',cursor:'pointer'}},'♡')
),
React.createElement('div',{style:{marginTop:12,fontSize:'var(--text-body-s)',color:'var(--text-primary)'}},name),
React.createElement('div',{style:{marginTop:2,fontSize:'var(--text-body-s)',color:'var(--text-muted)'}},price)
);
}
