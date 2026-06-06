const navLinks = [
  ['Home', 'landing'],
  ['Contact Us', 'contact'],
  ['Service', 'catalog'],
  ['About Us', 'about'],
]

export default function AuthHeader({ activeView, onNavigate }) {
  return (
    <header className="w-full top-0 sticky bg-background border-b border-on-surface/10 z-50 overflow-hidden">
      <div className="w-full min-h-24 px-sm md:px-margin py-md grid grid-cols-1 sm:grid-cols-[220px_minmax(0,1fr)] lg:grid-cols-[220px_minmax(560px,1fr)_280px] items-center gap-md lg:gap-lg">
        <button
          type="button"
          onClick={() => onNavigate('landing')}
          className="w-[220px] justify-self-start font-display-xl text-[32px] uppercase tracking-tighter text-primary text-left whitespace-nowrap"
        >
          MAD APE
        </button>

        <nav className="order-3 sm:col-span-2 lg:order-none lg:col-span-1 hidden md:grid grid-cols-4 items-center justify-self-center w-[560px]">
          {navLinks.map(([label, view]) => (
            <button
              key={label}
              type="button"
              onClick={() => onNavigate(view)}
              className="h-9 w-full inline-flex items-center justify-center text-center font-label-sm text-xs uppercase tracking-widest text-on-surface hover:text-secondary transition-colors whitespace-nowrap"
            >
              {label}
            </button>
          ))}
        </nav>

        <div className="flex w-full lg:w-[280px] items-center justify-start sm:justify-end justify-self-end gap-sm md:gap-md">
          {activeView === 'login' ? (
            <span className="w-20 h-9 inline-flex items-center justify-center text-center font-label-sm text-xs uppercase tracking-widest text-on-surface">
              Login
            </span>
          ) : (
            <button
              type="button"
              onClick={() => onNavigate('login')}
              className="w-20 h-9 inline-flex items-center justify-center text-center font-label-sm text-xs uppercase tracking-widest text-on-surface hover:text-secondary transition-colors"
            >
              Login
            </button>
          )}

          {activeView === 'register' ? (
            <span className="w-44 h-9 inline-flex items-center justify-center text-center bg-primary text-on-primary font-label-sm text-xs uppercase tracking-widest">
              Get Started
            </span>
          ) : (
            <button
              type="button"
              onClick={() => onNavigate('register')}
              className="w-44 h-9 inline-flex items-center justify-center text-center bg-secondary text-on-primary font-label-sm text-xs uppercase tracking-widest hover:bg-primary transition-colors"
            >
              Get Started
            </button>
          )}
        </div>
      </div>
    </header>
  )
}
