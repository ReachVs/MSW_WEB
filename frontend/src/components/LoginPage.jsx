import { useState } from 'react'

export default function LoginPage({ onLoginSuccess }) {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState('')
  const [rememberMe, setRememberMe] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setIsLoading(true)

    // Simulate login delay
    setTimeout(() => {
      if (email && password) {
        onLoginSuccess()
        setIsLoading(false)
      } else {
        setError('Please enter valid credentials')
        setIsLoading(false)
      }
    }, 1000)
  }

  return (
    <div className="w-full min-h-screen flex flex-col bg-background text-on-background font-body-md technical-grid">
      <header className="w-full top-0 sticky bg-background border-b border-on-surface/10 flex justify-between items-center px-margin py-md z-50">
        <div className="font-display-xl text-[32px] uppercase tracking-tighter text-primary">
          MAD APE
        </div>
        <nav className="hidden md:flex items-center gap-lg">
          {['Home', 'Contact Us', 'Service', 'About Us'].map((item) => (
            <span
              key={item}
              className="font-label-sm text-xs uppercase tracking-widest text-on-surface hover:text-secondary transition-colors"
            >
              {item}
            </span>
          ))}
        </nav>
        <div className="flex items-center gap-md">
          <span className="font-label-sm text-xs uppercase tracking-widest text-on-surface px-md py-sm">
            Login
          </span>
          <span className="bg-secondary text-on-primary font-label-sm text-xs uppercase tracking-widest px-lg py-sm">
            Get Started
          </span>
        </div>
      </header>

      <main className="flex-grow flex items-center justify-center p-margin">
        <div className="w-full max-w-[480px] flex flex-col items-center">
          <div className="text-center mb-xl">
            <h1 className="font-headline-lg text-4xl text-on-surface mb-xs uppercase">
              LOGIN
            </h1>
            <p className="font-label-sm text-xs uppercase tracking-widest text-on-surface-variant">
              Precision Engineering Dashboard
            </p>
          </div>

          <form
            onSubmit={handleSubmit}
            className="w-full bg-white border border-on-surface p-lg relative overflow-hidden flex flex-col gap-lg"
          >
            <div className="absolute top-0 right-0 w-12 h-12 border-t border-r border-secondary opacity-40"></div>
            <div className="absolute bottom-0 left-0 w-12 h-12 border-b border-l border-secondary opacity-40"></div>

            <div className="flex flex-col gap-xs">
              <label
                htmlFor="email"
                className="font-label-sm text-xs uppercase text-on-surface"
              >
                (Email)
              </label>
            <input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
                placeholder="OPERATOR@MADAPE.ENGINEERING"
                className="input-underline bg-transparent py-sm font-body-md text-base text-on-surface placeholder:text-outline-variant focus:outline-none w-full"
              disabled={isLoading}
            />
          </div>

            <div className="flex flex-col gap-xs">
              <div className="flex justify-between items-end">
                <label
                  htmlFor="password"
                  className="font-label-sm text-xs uppercase text-on-surface"
                >
                  (Password)
                </label>
                <button
                  type="button"
                  className="font-label-sm text-[10px] uppercase tracking-widest text-secondary hover:underline transition-all"
                >
                  Forget Password
                </button>
              </div>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
                placeholder="Access code"
                className="input-underline bg-transparent py-sm font-body-md text-base text-on-surface placeholder:text-outline-variant focus:outline-none w-full"
              disabled={isLoading}
            />
          </div>

            <div className="flex items-center justify-between mt-sm">
              <label className="flex items-center gap-sm cursor-pointer group">
            <input
              id="remember"
              type="checkbox"
              checked={rememberMe}
              onChange={(e) => setRememberMe(e.target.checked)}
                  className="w-4 h-4 text-secondary border-outline-variant focus:ring-0 cursor-pointer"
              disabled={isLoading}
            />
                <span className="font-label-sm text-xs uppercase text-on-surface-variant group-hover:text-on-surface transition-colors">
                  Keep Session Active
                </span>
              </label>
          </div>

          {error && (
              <div className="p-md bg-error-container border border-error text-on-error-container font-body-md text-sm flex items-start gap-2">
              <span className="material-symbols-outlined text-lg flex-shrink-0 mt-0.5">
                error
              </span>
              <span>{error}</span>
            </div>
          )}

          <button
            type="submit"
            disabled={isLoading}
              className="bg-secondary text-on-primary font-label-sm text-xs uppercase tracking-widest py-md px-lg w-full hover:bg-on-surface transition-all active:scale-[0.98] duration-300 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            {isLoading ? (
              <>
                <span className="inline-block w-4 h-4 border-2 border-on-secondary border-t-transparent animate-spin"></span>
                AUTHENTICATING
              </>
            ) : (
              <>
                <span className="material-symbols-outlined text-sm">
                  lock_open
                </span>
                  Initialize Session
              </>
            )}
          </button>
        </form>

          <div className="mt-lg flex flex-col items-center gap-sm">
            <span className="font-label-sm text-xs uppercase tracking-widest text-on-surface-variant">
              New Personnel?
            </span>
            <button className="border border-on-surface px-lg py-sm font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container-high transition-all">
              Establish Account
            </button>
          </div>
        </div>
      </main>

      <div className="fixed left-margin top-1/2 -translate-y-1/2 hidden lg:flex flex-col gap-lg opacity-20 pointer-events-none">
        <span className="font-label-sm text-[10px] rotate-90 origin-left uppercase tracking-widest whitespace-nowrap">
          Status: Ready
        </span>
        <div className="h-12 w-px bg-on-surface mx-auto"></div>
        <span className="font-label-sm text-[10px] rotate-90 origin-left uppercase tracking-widest whitespace-nowrap">
          Grid 40px // Baseline 4px
        </span>
      </div>

      <footer className="w-full mt-auto bg-primary border-t border-secondary/20 flex flex-col items-center justify-center py-lg px-margin gap-md">
        <div className="font-headline-lg text-3xl text-on-primary uppercase tracking-tighter">
          MAD APE
        </div>
        <div className="flex gap-lg flex-wrap justify-center">
          {[
            'Privacy Policy',
            'Terms of Service',
            'Technical Documentation',
            'Global Support',
          ].map((item) => (
            <span
              key={item}
              className="font-label-sm text-xs uppercase tracking-widest text-on-primary/60 hover:text-on-primary transition-colors"
            >
              {item}
            </span>
          ))}
        </div>
        <p className="font-label-sm text-xs uppercase tracking-widest text-on-primary/40 text-center">
          2026 MAD APE MOTORWORKS. ENGINEERED TO EXCELLENCE.
        </p>
      </footer>
    </div>
  )
}
