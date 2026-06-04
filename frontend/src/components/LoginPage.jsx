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
    <div className="w-full min-h-screen flex items-center justify-center bg-background overflow-hidden relative">
      {/* Decorative grid background */}
      <div className="absolute inset-0 grid-pattern opacity-50 pointer-events-none"></div>

      {/* Decorative shapes */}
      <div className="absolute -top-40 -right-40 w-80 h-80 bg-surface-container-high opacity-30 rounded-full blur-3xl"></div>
      <div className="absolute -bottom-40 -left-40 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>

      {/* Main Login Container */}
      <div className="relative z-10 w-full max-w-md px-lg">
        {/* Header */}
        <div className="mb-xl text-center">
          <h1 className="font-display-xl text-4xl md:text-5xl text-primary uppercase tracking-tighter font-black mb-md">
            MAD APE
          </h1>
          <p className="font-label-sm text-xs uppercase tracking-widest text-on-surface-variant">
            TELEMETRY PORTAL ACCESS
          </p>
        </div>

        {/* Login Form */}
        <form onSubmit={handleSubmit} className="space-y-md bg-surface-container-lowest border border-outline-variant p-lg">
          {/* Email Field */}
          <div className="flex flex-col gap-2">
            <label
              htmlFor="email"
              className="font-label-sm text-xs uppercase tracking-widest text-outline font-bold"
            >
              Email Address
            </label>
            <input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="member@madape.com"
              className="w-full px-sm py-3 bg-surface border border-outline-variant font-body-md text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary transition-all"
              disabled={isLoading}
            />
          </div>

          {/* Password Field */}
          <div className="flex flex-col gap-2">
            <label
              htmlFor="password"
              className="font-label-sm text-xs uppercase tracking-widest text-outline font-bold"
            >
              Access Code
            </label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="••••••••••••"
              className="w-full px-sm py-3 bg-surface border border-outline-variant font-body-md text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary transition-all"
              disabled={isLoading}
            />
          </div>

          {/* Remember Me */}
          <div className="flex items-center gap-2">
            <input
              id="remember"
              type="checkbox"
              checked={rememberMe}
              onChange={(e) => setRememberMe(e.target.checked)}
              className="w-4 h-4 text-secondary border-outline-variant focus:ring-0 cursor-pointer"
              disabled={isLoading}
            />
            <label
              htmlFor="remember"
              className="font-body-md text-sm text-on-surface-variant cursor-pointer"
            >
              Keep session active on this device
            </label>
          </div>

          {/* Error Message */}
          {error && (
            <div className="p-md bg-error-container border border-error text-on-error-container font-body-md text-sm flex items-start gap-2">
              <span className="material-symbols-outlined text-lg flex-shrink-0 mt-0.5">
                error
              </span>
              <span>{error}</span>
            </div>
          )}

          {/* Login Button */}
          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-3 px-lg bg-secondary text-on-secondary font-label-sm text-xs uppercase tracking-widest font-bold hover:bg-primary transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
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
                ACCESS TELEMETRY SUITE
              </>
            )}
          </button>

          {/* Forgot Password Link */}
          <div className="text-center">
            <a
              href="#"
              className="font-label-sm text-xs uppercase tracking-widest text-secondary hover:text-primary transition-colors"
            >
              Forgot Access Code?
            </a>
          </div>
        </form>

        {/* Demo Credentials */}
        <div className="mt-lg p-md bg-surface-container border border-outline-variant">
          <p className="font-label-sm text-[10px] uppercase tracking-widest text-outline mb-2 font-bold">
            DEMO CREDENTIALS
          </p>
          <div className="space-y-1">
            <div className="font-mono text-xs text-on-surface-variant">
              <strong>Email:</strong> demo@madape.com
            </div>
            <div className="font-mono text-xs text-on-surface-variant">
              <strong>Code:</strong> password123
            </div>
          </div>
        </div>

        {/* Support Links */}
        <div className="mt-lg flex justify-center gap-lg">
          <a
            href="#"
            className="font-label-sm text-xs uppercase tracking-widest text-on-surface-variant hover:text-primary transition-colors"
          >
            Support
          </a>
          <span className="text-outline-variant">•</span>
          <a
            href="#"
            className="font-label-sm text-xs uppercase tracking-widest text-on-surface-variant hover:text-primary transition-colors"
          >
            Documentation
          </a>
          <span className="text-outline-variant">•</span>
          <a
            href="#"
            className="font-label-sm text-xs uppercase tracking-widest text-on-surface-variant hover:text-primary transition-colors"
          >
            Contact
          </a>
        </div>
      </div>
    </div>
  )
}
