import { useEffect, useMemo, useState } from 'react'

function startOfMonth(date) {
  return new Date(date.getFullYear(), date.getMonth(), 1)
}

function endOfMonth(date) {
  return new Date(date.getFullYear(), date.getMonth() + 1, 0)
}

function startOfCalendar(date) {
  const monthStart = startOfMonth(date)
  const day = monthStart.getDay()
  const diff = day === 0 ? -6 : 1 - day
  const result = new Date(monthStart)
  result.setDate(monthStart.getDate() + diff)
  return result
}

function endOfCalendar(date) {
  const monthEnd = endOfMonth(date)
  const day = monthEnd.getDay()
  const diff = day === 0 ? 0 : 7 - day
  const result = new Date(monthEnd)
  result.setDate(monthEnd.getDate() + diff)
  return result
}

function formatDateKey(date) {
  return date.toISOString().slice(0, 10)
}

function formatReadableDate(date) {
  return new Intl.DateTimeFormat('en-GB', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

function isSameDay(firstDate, secondDate) {
  return formatDateKey(firstDate) === formatDateKey(secondDate)
}

export default function BookingScheduleModal({
  isOpen,
  onClose,
  onConfirm,
  bikeInfo,
  selectedServices,
  totalAmount,
}) {
  const [currentMonth, setCurrentMonth] = useState(() =>
    startOfMonth(new Date()),
  )
  const [selectedDate, setSelectedDate] = useState(() => new Date())
  const [monthAvailability, setMonthAvailability] = useState({})
  const [slots, setSlots] = useState([])
  const [selectedSlot, setSelectedSlot] = useState(null)
  const [notes, setNotes] = useState('')
  const [loadingMonth, setLoadingMonth] = useState(false)
  const [loadingSlots, setLoadingSlots] = useState(false)
  const [error, setError] = useState(null)

  const calendarDays = useMemo(() => {
    const start = startOfCalendar(currentMonth)
    const end = endOfCalendar(currentMonth)
    const days = []
    const cursor = new Date(start)

    while (cursor <= end) {
      days.push(new Date(cursor))
      cursor.setDate(cursor.getDate() + 1)
    }

    return days
  }, [currentMonth])

  useEffect(() => {
    if (!isOpen) return

    const today = new Date()
    setCurrentMonth(startOfMonth(today))
    setSelectedDate(today)
    setSelectedSlot(null)
    setNotes('')
    setError(null)
  }, [isOpen])

  useEffect(() => {
    if (!isOpen) return

    const fetchMonth = async () => {
      setLoadingMonth(true)
      try {
        const month = `${currentMonth.getFullYear()}-${String(currentMonth.getMonth() + 1).padStart(2, '0')}`
        const response = await fetch(
          `http://localhost:8080/api/calendar/month?month=${month}-01`,
        )
        if (!response.ok) {
          throw new Error(`Failed to load calendar: ${response.statusText}`)
        }

        const data = await response.json()
        const dayMap = Object.fromEntries(
          (data.data?.days || []).map((day) => [day.date, day]),
        )
        setMonthAvailability(dayMap)
      } catch (err) {
        setError(err.message)
      } finally {
        setLoadingMonth(false)
      }
    }

    fetchMonth()
  }, [currentMonth, isOpen])

  useEffect(() => {
    if (!isOpen || !selectedDate) return

    const fetchSlots = async () => {
      setLoadingSlots(true)
      setSelectedSlot(null)
      try {
        const response = await fetch(
          `http://localhost:8080/api/calendar/available-slots?date=${formatDateKey(selectedDate)}`,
        )
        if (!response.ok) {
          throw new Error(`Failed to load time slots: ${response.statusText}`)
        }

        const data = await response.json()
        setSlots(data.data?.slots || [])
      } catch (err) {
        setError(err.message)
        setSlots([])
      } finally {
        setLoadingSlots(false)
      }
    }

    fetchSlots()
  }, [selectedDate, isOpen])

  const handleConfirm = () => {
    if (!selectedDate || !selectedSlot) {
      setError('Please select an available date and time slot.')
      return
    }

    onConfirm({
      date: formatDateKey(selectedDate),
      time: selectedSlot.time,
      startsAt: `${formatDateKey(selectedDate)}T${selectedSlot.time}:00`,
      bookingTimeLabel: selectedSlot.label,
      notes: notes.trim(),
    })
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-md animate-fadeIn">
      <div className="max-h-[92vh] w-full max-w-6xl overflow-hidden border border-outline-variant bg-surface-container-lowest shadow-xl">
        <div className="border-b border-outline-variant p-lg">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <h2 className="font-headline-md text-lg font-bold uppercase tracking-tight text-primary">
                Select Date & Time
              </h2>
              <p className="mt-1 text-sm text-on-surface-variant">
                Choose an available workshop slot for {bikeInfo?.name}{' '}
                {bikeInfo?.model}
              </p>
            </div>
            <div className="w-full border border-outline-variant bg-white p-md lg:w-[280px]">
              <p className="text-xs font-label-sm uppercase tracking-widest text-outline">
                Booking Summary
              </p>
              <p className="mt-2 text-sm font-bold uppercase text-primary">
                {selectedServices?.length || 0} service(s)
              </p>
              <p className="mono-data mt-1 text-2xl font-bold text-secondary">
                ${Number(totalAmount || 0).toFixed(2)}
              </p>
            </div>
          </div>
        </div>

        <div className="grid max-h-[calc(92vh-170px)] grid-cols-1 overflow-y-auto lg:grid-cols-[minmax(0,1fr)_22rem]">
          <div className="border-r border-outline-variant p-lg">
            <div className="mb-md flex items-center justify-between">
              <button
                type="button"
                onClick={() =>
                  setCurrentMonth(
                    new Date(
                      currentMonth.getFullYear(),
                      currentMonth.getMonth() - 1,
                      1,
                    ),
                  )
                }
                className="border border-outline-variant px-sm py-xs font-label-sm uppercase tracking-widest hover:bg-surface-container-low"
              >
                Prev
              </button>
              <h3 className="font-headline-md text-primary uppercase">
                {new Intl.DateTimeFormat('en-GB', {
                  month: 'long',
                  year: 'numeric',
                }).format(currentMonth)}
              </h3>
              <button
                type="button"
                onClick={() =>
                  setCurrentMonth(
                    new Date(
                      currentMonth.getFullYear(),
                      currentMonth.getMonth() + 1,
                      1,
                    ),
                  )
                }
                className="border border-outline-variant px-sm py-xs font-label-sm uppercase tracking-widest hover:bg-surface-container-low"
              >
                Next
              </button>
            </div>

            <div className="grid grid-cols-7 gap-2">
              {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map((day) => (
                <div
                  key={day}
                  className="border border-outline-variant bg-surface-container-low p-2 text-center text-[10px] font-label-sm uppercase tracking-widest text-outline"
                >
                  {day}
                </div>
              ))}

              {calendarDays.map((date) => {
                const dayData = monthAvailability[formatDateKey(date)]
                const isPast = date < new Date(new Date().setHours(0, 0, 0, 0))
                const isFull = !!dayData?.is_full
                const isDisabled = isPast || isFull
                const isSelected = selectedDate && isSameDay(date, selectedDate)
                const isCurrentMonth =
                  date.getMonth() === currentMonth.getMonth()

                return (
                  <button
                    key={formatDateKey(date)}
                    type="button"
                    disabled={isDisabled}
                    onClick={() => {
                      setSelectedDate(date)
                      setError(null)
                    }}
                    className={`min-h-20 border p-2 text-left transition-colors ${
                      isSelected
                        ? 'border-secondary bg-secondary/10'
                        : 'border-outline-variant bg-white hover:bg-surface-container-low'
                    } ${!isCurrentMonth ? 'opacity-40' : ''} ${
                      isDisabled ? 'cursor-not-allowed opacity-40' : ''
                    }`}
                  >
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-primary">
                        {date.getDate()}
                      </span>
                      {(dayData?.booking_count || 0) > 0 && (
                        <span className="text-[10px] font-label-sm uppercase tracking-widest text-secondary">
                          {dayData.booking_count}
                        </span>
                      )}
                    </div>
                    {isFull && (
                      <span className="mt-2 block text-[10px] font-label-sm uppercase tracking-widest text-error">
                        Full
                      </span>
                    )}
                  </button>
                )
              })}
            </div>
            {loadingMonth && (
              <p className="mt-md text-sm text-outline">
                Loading month availability...
              </p>
            )}
          </div>

          <div className="space-y-md p-lg">
            <div>
              <p className="text-xs font-label-sm uppercase tracking-widest text-outline">
                Selected Date
              </p>
              <h3 className="mt-1 text-lg font-bold uppercase text-primary">
                {formatReadableDate(selectedDate)}
              </h3>
            </div>

            <div>
              <p className="mb-sm text-xs font-label-sm uppercase tracking-widest text-outline">
                Available Time Slots
              </p>
              <div className="grid grid-cols-2 gap-2">
                {slots.map((slot) => (
                  <button
                    key={slot.time}
                    type="button"
                    disabled={!slot.is_available}
                    onClick={() => {
                      setSelectedSlot(slot)
                      setError(null)
                    }}
                    className={`border p-sm text-left transition-colors ${
                      selectedSlot?.time === slot.time
                        ? 'border-secondary bg-secondary/10'
                        : 'border-outline-variant bg-white hover:bg-surface-container-low'
                    } ${!slot.is_available ? 'cursor-not-allowed opacity-40' : ''}`}
                  >
                    <p className="font-bold uppercase text-primary">
                      {slot.label}
                    </p>
                    <p className="text-[10px] uppercase tracking-widest text-outline">
                      {slot.remaining_capacity} left
                    </p>
                  </button>
                ))}
              </div>
              {loadingSlots && (
                <p className="mt-sm text-sm text-outline">
                  Loading time slots...
                </p>
              )}
              {!loadingSlots && slots.length === 0 && (
                <p className="mt-sm text-sm text-error">
                  No available slots for this day.
                </p>
              )}
            </div>

            <div className="border border-outline-variant bg-white p-md">
              <p className="text-xs font-label-sm uppercase tracking-widest text-outline">
                Continue Booking
              </p>
              <p className="mt-2 text-sm text-on-surface-variant">
                {selectedSlot
                  ? `Selected slot: ${formatReadableDate(selectedDate)} at ${selectedSlot.label}`
                  : 'Select one available time slot to continue.'}
              </p>
              <button
                type="button"
                onClick={handleConfirm}
                disabled={!selectedSlot}
                className="mt-md w-full bg-primary px-lg py-md text-xs font-label-sm uppercase tracking-widest text-on-primary transition-all hover:bg-secondary disabled:cursor-not-allowed disabled:opacity-50"
              >
                Continue
              </button>
            </div>

            <div>
              <label className="block text-xs font-label-sm uppercase tracking-widest text-outline">
                Booking Description
              </label>
              <textarea
                value={notes}
                onChange={(event) => setNotes(event.target.value)}
                rows={5}
                className="mt-sm w-full border border-outline-variant bg-white px-md py-sm text-sm focus:border-secondary focus:outline-none"
                placeholder="Describe the issue, special request, or symptoms."
              />
            </div>

            {error && <p className="text-sm text-error">{error}</p>}
          </div>
        </div>

        <div className="flex justify-end gap-3 border-t border-outline-variant p-lg">
          <button
            type="button"
            onClick={onClose}
            className="border border-outline-variant px-lg py-md text-xs font-label-sm uppercase tracking-widest hover:bg-surface-container-low"
          >
            Cancel
          </button>
          <button
            type="button"
            onClick={handleConfirm}
            disabled={!selectedSlot}
            className="bg-primary px-lg py-md text-xs font-label-sm uppercase tracking-widest text-on-primary hover:bg-secondary disabled:cursor-not-allowed disabled:opacity-50"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  )
}
