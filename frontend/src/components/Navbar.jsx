import { useState } from 'react'

export default function Navbar({
  isAuthenticated,
  currentView,
  onLogin,
  onRegister,
  onLogout,
  onNavigate,
}) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)

  const navLinks = [
    { label: 'HOME', view: 'landing' },
    { label: 'CONTACT US', view: 'contact' },
    { label: 'SERVICE', view: 'catalog' },
    { label: 'ABOUT US', view: 'about' },
  ]

  return (
    <nav className="w-full top-0 sticky z-50 bg-background border-b border-on-surface/10 overflow-hidden">
      <div className="w-full min-h-24 px-sm md:px-margin py-md grid grid-cols-1 sm:grid-cols-[300px_minmax(0,1fr)] lg:grid-cols-[300px_minmax(560px,1fr)_280px] items-center gap-md lg:gap-lg">
        <button
          type="button"
          className="flex w-[300px] items-center gap-3 justify-self-start text-primary cursor-pointer select-none text-left"
          onClick={() => onNavigate('landing')}
        >
          <img
            src="/madape-logo.PNG"
            alt="Mad Ape logo"
            className="h-12 w-auto object-contain"
          />
          <span className="font-display-xl text-[32px] uppercase tracking-tighter whitespace-nowrap">
            MAD APE
          </span>
        </button>

        {/* Desktop Navigation Links */}
        <div className="order-3 sm:col-span-2 lg:order-none lg:col-span-1 hidden md:grid grid-cols-4 items-center justify-self-center w-[560px]">
          {navLinks.map((link) => {
            const isActive = currentView === link.view
            return (
              <button
                key={link.label}
                onClick={() => onNavigate(link.view)}
                className={`relative h-9 w-full inline-flex items-center justify-center text-center font-label-sm text-xs uppercase tracking-widest transition-colors whitespace-nowrap ${
                  isActive
                    ? 'text-secondary'
                    : 'text-on-surface-variant hover:text-primary'
                }`}
              >
                {link.label}
                <span
                  className={`absolute bottom-0 left-1/2 h-0.5 w-16 -translate-x-1/2 bg-secondary transition-opacity ${
                    isActive ? 'opacity-100' : 'opacity-0'
                  }`}
                ></span>
              </button>
            )
          })}
        </div>

        {/* Right side buttons */}
        <div className="flex w-full lg:w-[280px] gap-sm md:gap-md items-center justify-start sm:justify-end justify-self-end">
          {isAuthenticated ? (
            <>
              <button
                onClick={onLogout}
                className="w-28 h-9 inline-flex items-center justify-center text-center bg-primary text-on-primary font-label-sm text-xs uppercase tracking-widest hover:bg-secondary transition-all active:scale-95"
              >
                Logout
              </button>
            </>
          ) : (
            <>
              <button
                onClick={onLogin}
                className="w-20 h-9 inline-flex items-center justify-center text-center font-label-sm text-xs uppercase tracking-widest text-on-surface-variant hover:text-primary transition-all"
              >
                Login
              </button>
              <button
                onClick={onRegister}
                className="w-44 h-9 inline-flex items-center justify-center text-center bg-secondary text-on-secondary font-label-sm text-xs uppercase tracking-widest hover:bg-primary transition-all active:scale-95"
              >
                Get Started
              </button>
            </>
          )}

          {/* Mobile menu toggle */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="md:hidden flex h-9 w-9 items-center justify-center text-primary hover:text-secondary transition-colors"
          >
            <span className="material-symbols-outlined text-2xl">
              {mobileMenuOpen ? 'close' : 'menu'}
            </span>
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden bg-background border-b border-on-surface/10 px-sm py-md flex flex-col gap-4 animate-fadeIn">
          {navLinks.map((link) => (
            <button
              key={link.label}
              onClick={() => {
                onNavigate(link.view)
                setMobileMenuOpen(false)
              }}
              className="text-left font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant hover:text-primary py-2"
            >
              {link.label}
            </button>
          ))}
        </div>
      )}
    </nav>
  )
}
