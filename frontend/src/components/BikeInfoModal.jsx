import { useState } from 'react'

export default function AddBikeModal({
  isOpen,
  onClose,
  onSubmit,
  existingBike = null,
}) {
  const [formData, setFormData] = useState(
    existingBike || {
      name: '',
      model: '',
      plateNumber: '',
      engineCapacity: '',
    },
  )

  const [errors, setErrors] = useState({})

  const validateForm = () => {
    const newErrors = {}

    if (!formData.name.trim()) {
      newErrors.name = 'Brand name is required'
    }
    if (!formData.model.trim()) {
      newErrors.model = 'Model is required'
    }
    if (!formData.plateNumber.trim()) {
      newErrors.plateNumber = 'Plate number is required'
    }
    if (!formData.engineCapacity || isNaN(formData.engineCapacity)) {
      newErrors.engineCapacity = 'Engine capacity must be a valid number'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = (e) => {
    e.preventDefault()

    if (!validateForm()) return

    onSubmit(formData)
    setFormData({
      name: '',
      model: '',
      plateNumber: '',
      engineCapacity: '',
    })
    setErrors({})
  }

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }))
    if (errors[name]) {
      setErrors((prev) => ({
        ...prev,
        [name]: '',
      }))
    }
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-md animate-fadeIn">
      <div className="bg-surface-container-lowest border border-outline-variant max-w-2xl w-full shadow-xl">
        <div className="p-lg border-b border-outline-variant">
          <h2 className="font-headline-md text-lg text-primary font-bold uppercase tracking-tight">
            {existingBike ? 'EDIT MOTORCYCLE INFO' : 'MOTORCYCLE INFO'}
          </h2>
        </div>

        <form onSubmit={handleSubmit} className="p-lg space-y-6">
          <div className="grid grid-cols-2 gap-6">
            <div>
              <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-3">
                Brand
              </label>
              <input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                className="w-full border border-outline-variant px-md py-3 font-body-md text-sm focus:outline-none focus:border-secondary transition-colors"
                placeholder="e.g., DUCATI"
              />
              {errors.name && (
                <p className="text-error text-xs mt-2 font-label-sm">
                  {errors.name}
                </p>
              )}
            </div>

            <div>
              <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-3">
                Model
              </label>
              <input
                type="text"
                name="model"
                value={formData.model}
                onChange={handleChange}
                className="w-full border border-outline-variant px-md py-3 font-body-md text-sm focus:outline-none focus:border-secondary transition-colors"
                placeholder="e.g., PANIGALE V4 S"
              />
              {errors.model && (
                <p className="text-error text-xs mt-2 font-label-sm">
                  {errors.model}
                </p>
              )}
            </div>
          </div>

          <div>
            <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-3">
              Plate Number
            </label>
            <input
              type="text"
              name="plateNumber"
              value={formData.plateNumber}
              onChange={handleChange}
              className="w-full border border-outline-variant px-md py-3 font-mono text-sm focus:outline-none focus:border-secondary transition-colors"
              placeholder="e.g., 1A-1234"
            />
            {errors.plateNumber && (
              <p className="text-error text-xs mt-2 font-label-sm">
                {errors.plateNumber}
              </p>
            )}
          </div>

          <div>
            <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-3">
              Engine Capacity (CC)
            </label>
            <input
              type="number"
              name="engineCapacity"
              value={formData.engineCapacity}
              onChange={handleChange}
              className="w-full border border-outline-variant px-md py-3 font-mono text-sm focus:outline-none focus:border-secondary transition-colors"
              placeholder="e.g., 1103"
            />
            {errors.engineCapacity && (
              <p className="text-error text-xs mt-2 font-label-sm">
                {errors.engineCapacity}
              </p>
            )}
          </div>

          <div className="flex gap-3 justify-end pt-6 border-t border-outline-variant">
            <button
              type="button"
              onClick={onClose}
              className="border border-outline-variant text-on-surface px-lg py-md font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container-low transition-all active:scale-[0.98]"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="bg-primary text-on-primary hover:bg-secondary px-lg py-md font-label-sm text-xs uppercase tracking-widest transition-all active:scale-[0.98]"
            >
              {existingBike ? 'Update Bike' : 'Add Bike'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}
