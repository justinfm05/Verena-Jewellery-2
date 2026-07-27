/* @ds-bundle: {"format":4,"namespace":"VerenaJewelleryDesignSystem_225011","components":[{"name":"Badge","sourcePath":"components/core/Badge.jsx"},{"name":"Button","sourcePath":"components/core/Button.jsx"},{"name":"Card","sourcePath":"components/core/Card.jsx"},{"name":"IconButton","sourcePath":"components/core/IconButton.jsx"},{"name":"Input","sourcePath":"components/core/Input.jsx"},{"name":"ProductCard","sourcePath":"components/core/ProductCard.jsx"},{"name":"Select","sourcePath":"components/core/Select.jsx"}],"sourceHashes":{"components/core/Badge.jsx":"9083898d2543","components/core/Button.jsx":"b91363ad8cd8","components/core/Card.jsx":"ae0fa6fda10c","components/core/IconButton.jsx":"d48efd346461","components/core/Input.jsx":"59a120302027","components/core/ProductCard.jsx":"7d9238c818fb","components/core/Select.jsx":"1d62555676f2"},"inlinedExternals":[],"unexposedExports":[]} */

(() => {

const __ds_ns = (window.VerenaJewelleryDesignSystem_225011 = window.VerenaJewelleryDesignSystem_225011 || {});

const __ds_scope = {};

(__ds_ns.__errors = __ds_ns.__errors || []);

// components/core/Badge.jsx
try { (() => {
function Badge({
  children,
  variant = 'neutral'
}) {
  const variants = {
    neutral: {
      background: 'var(--neutral-100)',
      color: 'var(--text-secondary)'
    },
    accent: {
      background: 'var(--gold-200)',
      color: 'var(--green-800)'
    },
    inverse: {
      background: 'var(--green-700)',
      color: 'var(--gold-300)'
    }
  };
  const v = variants[variant] || variants.neutral;
  return React.createElement('span', {
    style: {
      ...v,
      display: 'inline-flex',
      alignItems: 'center',
      padding: '4px 12px',
      borderRadius: 'var(--radius-pill)',
      fontFamily: 'var(--font-body)',
      fontSize: 11,
      letterSpacing: 'var(--tracking-wide)',
      textTransform: 'uppercase'
    }
  }, children);
}
Object.assign(__ds_scope, { Badge });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Badge.jsx", error: String((e && e.message) || e) }); }

// components/core/Button.jsx
try { (() => {
const base = {
  fontFamily: 'var(--font-body)',
  fontSize: 'var(--text-body-s)',
  letterSpacing: 'var(--tracking-normal)',
  border: 'none',
  cursor: 'pointer',
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  gap: 8,
  transition: 'transform var(--duration-fast) var(--ease-standard),background var(--duration-fast) var(--ease-standard),opacity var(--duration-fast) var(--ease-standard)'
};
const sizes = {
  s: {
    padding: '8px 16px',
    fontSize: '12px'
  },
  m: {
    padding: '12px 24px',
    fontSize: '13px'
  },
  l: {
    padding: '16px 32px',
    fontSize: '14px'
  }
};
const variants = {
  primary: {
    background: 'var(--green-700)',
    color: 'var(--gold-300)',
    borderRadius: 'var(--radius-pill)'
  },
  secondary: {
    background: 'transparent',
    color: 'var(--text-primary)',
    border: '1px solid var(--border-strong)',
    borderRadius: 'var(--radius-pill)'
  },
  ghost: {
    background: 'transparent',
    color: 'var(--text-primary)',
    borderRadius: 'var(--radius-pill)'
  },
  accent: {
    background: 'var(--accent)',
    color: 'var(--green-900)',
    borderRadius: 'var(--radius-pill)'
  }
};
function Button({
  variant = 'primary',
  size = 'm',
  disabled = false,
  children,
  onClick,
  style
}) {
  const v = variants[variant] || variants.primary;
  const s = sizes[size] || sizes.m;
  const [hover, setHover] = React.useState(false);
  const [active, setActive] = React.useState(false);
  let bg = v.background;
  if (!disabled && hover) {
    if (variant === 'primary') bg = 'var(--green-600)';
    if (variant === 'accent') bg = 'var(--accent-hover)';
    if (variant === 'secondary' || variant === 'ghost') bg = 'var(--neutral-100)';
  }
  return React.createElement('button', {
    style: {
      ...base,
      ...s,
      ...v,
      background: bg,
      textTransform: 'uppercase',
      fontWeight: 500,
      opacity: disabled ? 0.4 : 1,
      cursor: disabled ? 'not-allowed' : 'pointer',
      transform: active && !disabled ? 'scale(0.98)' : 'scale(1)',
      ...style
    },
    disabled,
    onClick,
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => {
      setHover(false);
      setActive(false);
    },
    onMouseDown: () => setActive(true),
    onMouseUp: () => setActive(false)
  }, children);
}
Object.assign(__ds_scope, { Button });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Button.jsx", error: String((e && e.message) || e) }); }

// components/core/Card.jsx
try { (() => {
function Card({
  children,
  style
}) {
  return React.createElement('div', {
    style: {
      background: 'var(--surface-card)',
      borderRadius: 'var(--radius-m)',
      boxShadow: 'var(--shadow-card)',
      padding: 'var(--space-5)',
      ...style
    }
  }, children);
}
Object.assign(__ds_scope, { Card });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Card.jsx", error: String((e && e.message) || e) }); }

// components/core/IconButton.jsx
try { (() => {
function IconButton({
  icon,
  label,
  variant = 'ghost',
  onClick,
  style
}) {
  const [hover, setHover] = React.useState(false);
  const ref = React.useRef(null);
  const dark = variant === 'inverse';
  React.useEffect(() => {
    if (window.lucide) window.lucide.createIcons({
      nameAttr: 'data-lucide',
      attrs: {
        width: 18,
        height: 18
      }
    });
  }, [icon]);
  return React.createElement('button', {
    'aria-label': label,
    title: label,
    onClick,
    ref,
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false),
    style: {
      width: 40,
      height: 40,
      borderRadius: '50%',
      border: dark ? '1px solid rgba(242,226,186,0.3)' : '1px solid var(--border-subtle)',
      background: hover ? dark ? 'rgba(242,226,186,0.12)' : 'var(--neutral-100)' : 'transparent',
      color: dark ? 'var(--text-inverse)' : 'var(--text-primary)',
      display: 'inline-flex',
      alignItems: 'center',
      justifyContent: 'center',
      cursor: 'pointer',
      transition: 'background var(--duration-fast) var(--ease-standard)',
      ...style
    }
  }, React.createElement('i', {
    'data-lucide': icon,
    style: {
      width: 18,
      height: 18,
      display: 'inline-block'
    }
  }));
}
Object.assign(__ds_scope, { IconButton });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/IconButton.jsx", error: String((e && e.message) || e) }); }

// components/core/Input.jsx
try { (() => {
function Input({
  label,
  placeholder,
  type = 'text',
  value,
  onChange
}) {
  const [focus, setFocus] = React.useState(false);
  return React.createElement('label', {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 6,
      fontFamily: 'var(--font-body)'
    }
  }, label && React.createElement('span', {
    style: {
      fontSize: 11,
      letterSpacing: 'var(--tracking-wide)',
      textTransform: 'uppercase',
      color: 'var(--text-muted)'
    }
  }, label), React.createElement('input', {
    type,
    placeholder,
    value,
    onChange,
    onFocus: () => setFocus(true),
    onBlur: () => setFocus(false),
    style: {
      border: 'none',
      borderBottom: `1px solid ${focus ? 'var(--accent)' : 'var(--border-strong)'}`,
      background: 'transparent',
      padding: '10px 2px',
      fontSize: 'var(--text-body)',
      color: 'var(--text-primary)',
      outline: 'none',
      transition: 'border-color var(--duration-fast) var(--ease-standard)'
    }
  }));
}
Object.assign(__ds_scope, { Input });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Input.jsx", error: String((e && e.message) || e) }); }

// components/core/ProductCard.jsx
try { (() => {
function ProductCard({
  name,
  price,
  badge,
  onAdd
}) {
  const [hover, setHover] = React.useState(false);
  return React.createElement('div', {
    style: {
      fontFamily: 'var(--font-body)'
    },
    onMouseEnter: () => setHover(true),
    onMouseLeave: () => setHover(false)
  }, React.createElement('div', {
    style: {
      position: 'relative',
      aspectRatio: '4/5',
      background: 'var(--neutral-100)',
      borderRadius: 'var(--radius-m)',
      overflow: 'hidden',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      color: 'var(--text-muted)',
      fontSize: 12
    }
  }, 'product photo', badge && React.createElement('span', {
    style: {
      position: 'absolute',
      top: 12,
      left: 12,
      background: 'var(--gold-200)',
      color: 'var(--green-800)',
      fontSize: 10,
      letterSpacing: 'var(--tracking-wide)',
      textTransform: 'uppercase',
      padding: '4px 10px',
      borderRadius: 'var(--radius-pill)'
    }
  }, badge), React.createElement('button', {
    'aria-label': 'Save to wishlist',
    style: {
      position: 'absolute',
      top: 8,
      right: 8,
      width: 32,
      height: 32,
      borderRadius: '50%',
      border: 'none',
      background: hover ? '#fff' : 'transparent',
      opacity: hover ? 1 : 0,
      transition: 'opacity var(--duration-fast) var(--ease-standard),background var(--duration-fast) var(--ease-standard)',
      cursor: 'pointer'
    }
  }, '♡')), React.createElement('div', {
    style: {
      marginTop: 12,
      fontSize: 'var(--text-body-s)',
      color: 'var(--text-primary)'
    }
  }, name), React.createElement('div', {
    style: {
      marginTop: 2,
      fontSize: 'var(--text-body-s)',
      color: 'var(--text-muted)'
    }
  }, price));
}
Object.assign(__ds_scope, { ProductCard });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/ProductCard.jsx", error: String((e && e.message) || e) }); }

// components/core/Select.jsx
try { (() => {
function Select({
  label,
  options = [],
  value,
  onChange
}) {
  return React.createElement('label', {
    style: {
      display: 'flex',
      flexDirection: 'column',
      gap: 6,
      fontFamily: 'var(--font-body)'
    }
  }, label && React.createElement('span', {
    style: {
      fontSize: 11,
      letterSpacing: 'var(--tracking-wide)',
      textTransform: 'uppercase',
      color: 'var(--text-muted)'
    }
  }, label), React.createElement('select', {
    value,
    onChange,
    style: {
      border: 'none',
      borderBottom: '1px solid var(--border-strong)',
      background: 'transparent',
      padding: '10px 2px',
      fontSize: 'var(--text-body)',
      color: 'var(--text-primary)',
      outline: 'none',
      fontFamily: 'var(--font-body)'
    }
  }, options.map(o => React.createElement('option', {
    key: o,
    value: o
  }, o))));
}
Object.assign(__ds_scope, { Select });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Select.jsx", error: String((e && e.message) || e) }); }

__ds_ns.Badge = __ds_scope.Badge;

__ds_ns.Button = __ds_scope.Button;

__ds_ns.Card = __ds_scope.Card;

__ds_ns.IconButton = __ds_scope.IconButton;

__ds_ns.Input = __ds_scope.Input;

__ds_ns.ProductCard = __ds_scope.ProductCard;

__ds_ns.Select = __ds_scope.Select;

})();
