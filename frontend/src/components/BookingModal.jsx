import { useState } from 'react'

export default function BookingModal({ isOpen, onClose, onSubmit, bikes }) {
  const [selectedBike, setSelectedBike] = useState('')
  const [customBike, setCustomBike] = useState('')
  const [serviceType, setServiceType] = useState('Desmo Service')
  const [date, setDate] = useState('')
  const [notes, setNotes] = useState('')

  if (!isOpen) return null

  const serviceOptions = [
    'Desmo Service',
    'ECU Tuning',
    'Fluid Flush',
    'Engine Stripdown',
    'General Diagnostics',
    'Custom Fabrication',
  ]

  const handleSubmit = (e) => {
    e.preventDefault()
    const bikeName =
      selectedBike === 'custom' || !selectedBike
        ? customBike || 'Custom Machine'
        : selectedBike
    onSubmit({
      bikeName,
      serviceType,
      date: date
        ? new Date(date)
            .toLocaleDateString('en-US', {
              day: 'numeric',
              month: 'short',
              year: 'numeric',
            })
            .toUpperCase()
        : 'IMMEDIATE',
      notes,
    })
    // Reset state
    setSelectedBike('')
    setCustomBike('')
    setDate('')
    setNotes('')
    onClose()
  }

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center bg-primary/70 backdrop-blur-sm p-md">
      <div
        className="w-full max-w-[32rem] bg-background border border-primary p-lg relative animate-scaleIn shadow-2xl"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 text-on-surface-variant hover:text-secondary transition-colors"
        >
          <span className="material-symbols-outlined text-2xl">close</span>
        </button>

        {/* Title */}
        <div className="mb-6">
          <div className="bg-primary text-on-primary inline-block px-sm py-xs mb-2 font-label-sm text-[10px] uppercase tracking-[0.2em]">
            Booking Portal
          </div>
          <h2 className="font-headline-lg text-2xl text-primary uppercase tracking-tight">
            Schedule Service
          </h2>
          <div className="w-16 h-1 bg-secondary mt-2"></div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="space-y-md">
          {/* Select Bike */}
          <div className="flex flex-col gap-xs">
            <label className="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant">
              Select Machine
            </label>
            <select
              value={selectedBike}
              onChange={(e) => setSelectedBike(e.target.value)}
              className="bg-white border border-outline-variant p-sm font-body-md text-sm outline-none focus:border-secondary transition-colors"
              required
            >
              <option value="">-- Choose Machine --</option>
              {bikes.map((bike) => (
                <option key={bike.id} value={bike.name}>
                  {bike.name}
                </option>
              ))}
              <option value="custom">Other / Custom Machine</option>
            </select>
          </div>

          {/* Custom Bike Input if selected */}
          {selectedBike === 'custom' && (
            <div className="flex flex-col gap-xs animate-fadeIn">
              <label className="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant">
                Machine Specification
              </label>
              <input
                type="text"
                placeholder="e.g. DUCATI PANIGALE 899"
                value={customBike}
                onChange={(e) => setCustomBike(e.target.value)}
                className="bg-white border border-outline-variant p-sm font-body-md text-sm outline-none focus:border-secondary transition-colors"
                required
              />
            </div>
          )}

          {/* Service Type */}
          <div className="flex flex-col gap-xs">
            <label className="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant">
              Service Class
            </label>
            <select
              value={serviceType}
              onChange={(e) => setServiceType(e.target.value)}
              className="bg-white border border-outline-variant p-sm font-body-md text-sm outline-none focus:border-secondary transition-colors"
              required
            >
              {serviceOptions.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </select>
          </div>

          {/* Preferred Date */}
          <div className="flex flex-col gap-xs">
            <label className="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant">
              Preferred Service Date
            </label>
            <input
              type="date"
              value={date}
              onChange={(e) => setDate(e.target.value)}
              className="bg-white border border-outline-variant p-sm font-body-md text-sm outline-none focus:border-secondary transition-colors"
              required
            />
          </div>

          {/* Special Notes */}
          <div className="flex flex-col gap-xs">
            <label className="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant">
              Technical Directives / Notes
            </label>
            <textarea
              placeholder="Specify tolerance standards, special requests, or historic logs..."
              rows={3}
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              className="bg-white border border-outline-variant p-sm font-body-md text-sm outline-none focus:border-secondary transition-colors resize-none"
            />
          </div>

          {/* Submit */}
          <div className="pt-2 flex justify-end gap-md">
            <button
              type="button"
              onClick={onClose}
              className="border border-primary text-primary px-lg py-sm font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container-low transition-all"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="bg-secondary text-on-secondary px-lg py-sm font-label-sm text-xs uppercase tracking-widest hover:bg-primary transition-all active:scale-95"
            >
              Submit Order
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}
