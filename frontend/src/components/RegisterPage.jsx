import { useState } from 'react'
import AuthFooter from './AuthFooter'
import AuthHeader from './AuthHeader'
import { saveStoredProfile } from '../utils/profileStorage'

function buildProfileFromName(fullName, email) {
  const parts = fullName.trim().split(/\s+/).filter(Boolean)
  const [firstName = 'Customer', ...lastNameParts] = parts

  return saveStoredProfile({
    firstName,
    lastName: lastNameParts.join(' '),
    email,
  })
}

export default function RegisterPage({ onRegisterSuccess, onNavigate }) {
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    password: '',
    confirmPassword: '',
  })
  const [rememberMe, setRememberMe] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState('')

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }))
    if (error) setError('')
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')

    if (
      !formData.fullName.trim() ||
      !formData.email.trim() ||
      !formData.password ||
      !formData.confirmPassword
    ) {
      setError('Please complete all registration fields')
      return
    }

    if (formData.password !== formData.confirmPassword) {
      setError('Password confirmation does not match')
      return
    }

    setIsLoading(true)

    try {
      const response = await fetch('http://localhost:8080/api/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          name: formData.fullName.trim(),
          email: formData.email.trim(),
          password: formData.password,
          password_confirmation: formData.confirmPassword,
        }),
      })

      const data = await response.json()

      if (!response.ok) {
        const validationMessage = Object.values(data.errors || {})
          .flat()
          .join(' ')

        setError(
          validationMessage ||
            data.message ||
            'Registration failed. Please try again.',
        )
        return
      }

      localStorage.setItem('authToken', data.access_token)
      const savedProfile = buildProfileFromName(
        formData.fullName,
        formData.email.trim(),
      )

      onRegisterSuccess(savedProfile)
    } catch (err) {
      setError('Network error. Please try again later.')
      console.error('Registration network error:', err)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="w-full min-h-screen flex flex-col bg-background text-on-background font-body-md technical-grid">
      <AuthHeader activeView="register" onNavigate={onNavigate} />

      <main className="flex-grow flex flex-col items-center p-margin">
        <div className="w-full max-w-[480px] flex flex-col items-center mt-lg">
          <div className="text-center mb-xl">
            <h1 className="font-headline-lg text-4xl text-on-surface mb-xs uppercase font-black">
              SIGN UP
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
                htmlFor="fullName"
                className="font-label-sm text-xs uppercase text-on-surface"
              >
                (Full Name)
              </label>
              <input
                id="fullName"
                name="fullName"
                type="text"
                value={formData.fullName}
                onChange={handleChange}
                placeholder="ENTER FULL NAME"
                className="border border-outline px-sm py-sm font-body-md text-base text-on-surface placeholder:text-outline-variant focus:outline-none focus:border-secondary w-full"
                disabled={isLoading}
              />
            </div>

            <div className="flex flex-col gap-xs">
              <label
                htmlFor="registerEmail"
                className="font-label-sm text-xs uppercase text-on-surface"
              >
                (Email)
              </label>
              <input
                id="registerEmail"
                name="email"
                type="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="OPERATOR@MADAPE.ENGINEERING"
                className="border border-outline px-sm py-sm font-body-md text-base text-on-surface placeholder:text-outline-variant focus:outline-none focus:border-secondary w-full"
                disabled={isLoading}
              />
            </div>

            <div className="flex flex-col gap-xs">
              <label
                htmlFor="registerPassword"
                className="font-label-sm text-xs uppercase text-on-surface"
              >
                (Password)
              </label>
              <input
                id="registerPassword"
                name="password"
                type="password"
                value={formData.password}
                onChange={handleChange}
                placeholder="Access code"
                className="border border-outline px-sm py-sm font-body-md text-base text-on-surface placeholder:text-outline-variant focus:outline-none focus:border-secondary w-full"
                disabled={isLoading}
              />
            </div>

            <div className="flex flex-col gap-xs">
              <label
                htmlFor="confirmPassword"
                className="font-label-sm text-xs uppercase text-on-surface"
              >
                (Confirm Password)
              </label>
              <input
                id="confirmPassword"
                name="confirmPassword"
                type="password"
                value={formData.confirmPassword}
                onChange={handleChange}
                placeholder="Access code"
                className="border border-outline px-sm py-sm font-body-md text-base text-on-surface placeholder:text-outline-variant focus:outline-none focus:border-secondary w-full"
                disabled={isLoading}
              />
            </div>

            <label className="flex items-center gap-sm cursor-pointer group mt-sm">
              <input
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
              className="bg-secondary text-on-secondary font-label-sm text-xs uppercase tracking-widest py-md px-lg w-full hover:bg-primary transition-all active:scale-[0.98] duration-300 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              {isLoading ? (
                <>
                  <span className="inline-block w-4 h-4 border-2 border-on-secondary border-t-transparent animate-spin"></span>
                  Creating Account
                </>
              ) : (
                'Create Account'
              )}
            </button>
          </form>

          <div className="mt-lg flex flex-col items-center gap-sm">
            <span className="font-label-sm text-xs uppercase tracking-widest text-on-surface-variant">
              Already Have An Account?
            </span>
            <button
              type="button"
              onClick={() => onNavigate('login')}
              className="border border-on-surface px-lg py-sm font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container-high transition-all min-w-36"
            >
              Login
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

      <AuthFooter />
    </div>
  )
}
