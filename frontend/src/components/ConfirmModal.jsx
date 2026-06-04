export default function ConfirmModal({
  isOpen,
  title = 'Confirm Action',
  message = 'Are you sure you want to proceed?',
  confirmText = 'Confirm',
  cancelText = 'Cancel',
  onConfirm,
  onCancel,
  isDangerous = false,
}) {
  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-md animate-fadeIn">
      <div className="bg-surface-container-lowest border border-outline-variant max-w-[28rem] w-full shadow-xl">
        <div className="p-lg">
          <h2 className="font-headline-md text-lg text-primary font-bold uppercase mb-3 tracking-tight">
            {title}
          </h2>
          <p className="text-on-surface-variant font-body-md text-sm mb-lg leading-relaxed">
            {message}
          </p>

          <div className="flex gap-3 justify-end">
            <button
              onClick={onCancel}
              className="border border-outline-variant text-on-surface px-lg py-md font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container-low transition-all active:scale-[0.98]"
            >
              {cancelText}
            </button>
            <button
              onClick={onConfirm}
              className={`px-lg py-md font-label-sm text-xs uppercase tracking-widest transition-all active:scale-[0.98] ${
                isDangerous
                  ? 'bg-error text-on-error hover:bg-error border border-error'
                  : 'bg-primary text-on-primary hover:bg-secondary border border-primary'
              }`}
            >
              {confirmText}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
