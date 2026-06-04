import { useState } from 'react'

export default function Navbar({
  isAuthenticated,
  currentView,
  onLogin,
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
    <nav className="w-full top-0 sticky z-50 bg-background border-b border-on-surface/10">
      <div className="flex justify-between items-center px-margin py-md w-full max-w-screen-2xl mx-auto">
        <div
          className="font-display-xl text-[32px] uppercase tracking-tighter text-primary cursor-pointer select-none"
          onClick={() => onNavigate('landing')}
        >
          MAD APE
        </div>

        {/* Desktop Navigation Links */}
        <div className="hidden md:flex gap-lg items-center">
          {navLinks.map((link) => {
            const isActive = currentView === link.view
            return (
              <button
                key={link.label}
                onClick={() => onNavigate(link.view)}
                className={`font-label-sm text-label-sm uppercase tracking-widest transition-colors pb-1 border-b-2 ${
                  isActive
                    ? 'text-secondary border-secondary'
                    : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/30'
                }`}
              >
                {link.label}
              </button>
            )
          })}
        </div>

        {/* Right side buttons */}
        <div className="flex gap-md items-center">
          {isAuthenticated ? (
            <>
              <button
                onClick={() => onNavigate('garage')}
                className="hidden sm:inline-block font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant hover:text-primary transition-all"
              >
                My Garage
              </button>
              <button
                onClick={onLogout}
                className="bg-primary text-on-primary px-lg py-sm font-label-sm text-label-sm uppercase tracking-widest hover:bg-secondary transition-all active:scale-95"
              >
                Logout
              </button>
            </>
          ) : (
            <>
              <button
                onClick={onLogin}
                className="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant hover:text-primary transition-all"
              >
                Login
              </button>
              <button
                onClick={onLogin}
                className="bg-secondary text-on-secondary px-lg py-sm font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary transition-all active:scale-95"
              >
                Get Started
              </button>
            </>
          )}

          {/* Mobile menu toggle */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="md:hidden flex items-center p-1 text-primary hover:text-secondary transition-colors"
          >
            <span className="material-symbols-outlined text-2xl">
              {mobileMenuOpen ? 'close' : 'menu'}
            </span>
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden bg-background border-b border-on-surface/10 px-margin py-md flex flex-col gap-4 animate-fadeIn">
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
          {isAuthenticated && (
            <button
              onClick={() => {
                onNavigate('garage')
                setMobileMenuOpen(false)
              }}
              className="text-left font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant hover:text-primary py-2"
            >
              My Garage
            </button>
          )}
        </div>
      )}
    </nav>
  )
}
