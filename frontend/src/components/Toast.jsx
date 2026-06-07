import { useEffect, useState } from 'react'

export default function Toast({
  message,
  type = 'info',
  duration = 3000,
  onClose,
}) {
  const [isVisible, setIsVisible] = useState(true)

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsVisible(false)
      onClose?.()
    }, duration)

    return () => clearTimeout(timer)
  }, [duration, onClose])

  if (!isVisible) return null

  const baseStyles =
    'fixed bottom-lg right-lg px-lg py-md font-label-sm text-xs uppercase tracking-widest z-50 shadow-lg border transition-all duration-300 animate-fadeIn'

  const typeStyles = {
    success: 'bg-green-600 text-white border-green-700',
    error: 'bg-error text-on-error border-error',
    info: 'bg-primary text-on-primary border-primary',
  }

  return (
    <div className={`${baseStyles} ${typeStyles[type]}`}>
      <div className="flex items-center gap-3">
        {type === 'success' && <span className="text-lg">✓</span>}
        {type === 'error' && <span className="text-lg">✕</span>}
        {type === 'info' && <span className="text-lg">ℹ</span>}
        <span>{message}</span>
      </div>
    </div>
  )
}
