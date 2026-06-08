import { useEffect, useState } from 'react'
import BikeInfoModal from './BikeInfoModal'
import BookingScheduleModal from './BookingScheduleModal'
import ServiceSelectionModal from './ServiceSelectionModal'
import Toast from './Toast'
import ConfirmModal from './ConfirmModal'
import {
  getProfileDisplayName,
  getStoredProfile,
} from '../utils/profileStorage'

const GARAGE_STORAGE_KEY = 'msw-garage-bookings'
const DEFAULT_BIKE_IMAGE =
  'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&h=700&fit=crop'

function getBikeSortValue(bike) {
  if (bike.createdAt) {
    const timestamp = new Date(bike.createdAt).getTime()
    if (!Number.isNaN(timestamp)) return timestamp
  }

  return Number(bike.bookingId ?? bike.id ?? 0)
}

function sortBikesNewestFirst(bikes) {
  return [...bikes].sort((firstBike, secondBike) => {
    return getBikeSortValue(secondBike) - getBikeSortValue(firstBike)
  })
}

function formatStatusLabel(status) {
  if (status === 'pending') return 'Pending'
  if (status === 'confirmed') return 'Confirmed'
  if (status === 'repair') return 'Repair'
  if (status === 'waiting_part') return 'Waiting Part'
  if (status === 'ready_pickup') return 'Ready for Pickup'
  if (status === 'completed') return 'Completed'
  if (status === 'cancelled') return 'Cancelled'
  return (status || 'Pending').replace(/_/g, ' ')
}

function getMilestone(status) {
  if (status === 'pending') return 'Booking received and awaiting review'
  if (status === 'confirmed') return 'Booking accepted and scheduled'
  if (status === 'repair') return 'Motorcycle is currently in repair'
  if (status === 'waiting_part') return 'Repair paused while waiting for parts'
  if (status === 'ready_pickup')
    return 'Service completed and ready for collection'
  if (status === 'completed') return 'Work order completed'
  if (status === 'cancelled') return 'Booking cancelled'
  return 'Booking created'
}

function canCustomerCancel(status) {
  return status === 'pending' || status === 'confirmed'
}

function getStatusBadgeClass(status) {
  if (status === 'pending') return 'bg-[#6B7280] text-white'
  if (status === 'confirmed') return 'bg-[#2563EB] text-white'
  if (status === 'repair') return 'bg-[#F59E0B] text-white'
  if (status === 'waiting_part') return 'bg-[#8B5CF6] text-white'
  if (status === 'ready_pickup') return 'bg-[#10B981] text-white'
  if (status === 'completed') return 'bg-[#059669] text-white'
  if (status === 'cancelled') return 'bg-[#EF4444] text-white'
  return 'bg-primary text-on-primary'
}

function getStatusPanelClass(status) {
  if (status === 'pending')
    return 'border-[#6B7280]/30 bg-[#6B7280]/10 text-[#6B7280]'
  if (status === 'confirmed')
    return 'border-[#2563EB]/30 bg-[#2563EB]/10 text-[#2563EB]'
  if (status === 'repair')
    return 'border-[#F59E0B]/30 bg-[#F59E0B]/10 text-[#F59E0B]'
  if (status === 'waiting_part')
    return 'border-[#8B5CF6]/30 bg-[#8B5CF6]/10 text-[#8B5CF6]'
  if (status === 'ready_pickup')
    return 'border-[#10B981]/30 bg-[#10B981]/10 text-[#10B981]'
  if (status === 'completed')
    return 'border-[#059669]/30 bg-[#059669]/10 text-[#059669]'
  if (status === 'cancelled')
    return 'border-[#EF4444]/30 bg-[#EF4444]/10 text-[#EF4444]'
  return 'border-secondary/20 bg-secondary/[0.02] text-secondary'
}

function normalizeSelectedServices(booking) {
  if (
    Array.isArray(booking.selected_services) &&
    booking.selected_services.length > 0
  ) {
    return booking.selected_services.map((service) => ({
      id: service.id ?? `${booking.id}-${service.name}`,
      name: service.name,
      price: Number(service.price || 0),
    }))
  }

  return booking.service_name
    ? [
        {
          id: booking.service_id ?? `${booking.id}-service`,
          name: booking.service_name,
          price: Number(booking.total_amount || 0),
        },
      ]
    : []
}

function mapBookingToBike(booking) {
  const selectedServices = normalizeSelectedServices(booking)
  const totalAmount =
    booking.total_amount !== null && booking.total_amount !== undefined
      ? Number(booking.total_amount)
      : selectedServices.reduce(
          (sum, service) => sum + Number(service.price || 0),
          0,
        )

  return {
    id: booking.id,
    bookingId: booking.id,
    name: booking.bike_name,
    model: booking.model,
    plateNumber: booking.plate_number,
    engineCapacity: booking.engine_capacity || 'N/A',
    customerName: booking.customer_name || 'N/A',
    customerEmail: booking.customer_email || 'N/A',
    bookingDate:
      booking.booking_date || booking.starts_at?.slice(0, 10) || null,
    bookingTimeLabel: booking.booking_time_label || 'N/A',
    serviceStatus: formatStatusLabel(booking.status),
    status: booking.status,
    serviceType:
      selectedServices.length > 1
        ? `${selectedServices.length} catalog services`
        : selectedServices[0]?.name ||
          booking.service_name ||
          'Service Booking',
    lead: 'Unassigned',
    milestone: getMilestone(booking.status),
    image: DEFAULT_BIKE_IMAGE,
    selectedServices,
    totalAmount,
    notes: booking.notes,
    createdAt: booking.created_at ?? new Date().toISOString(),
    canCancel: booking.can_customer_cancel ?? canCustomerCancel(booking.status),
  }
}

function loadStoredBikes() {
  try {
    const raw = localStorage.getItem(GARAGE_STORAGE_KEY)
    if (!raw) return []

    const parsed = JSON.parse(raw)
    return Array.isArray(parsed) ? sortBikesNewestFirst(parsed) : []
  } catch {
    return []
  }
}

export default function GaragePage() {
  const [expandedBikeId, setExpandedBikeId] = useState(null)
  const [serviceRequestStep, setServiceRequestStep] = useState('closed')
  const [currentBikeInfo, setCurrentBikeInfo] = useState(null)
  const [pendingBookingRequest, setPendingBookingRequest] = useState(null)
  const [bikes, setBikes] = useState([])
  const [loading, setLoading] = useState(true)
  const [toast, setToast] = useState(null)
  const [confirmCancel, setConfirmCancel] = useState(null)
  const [sensors, setSensors] = useState({
    bmwTireTemp: { f: 32.0, r: 34.0 },
    triumphVoltage: 13.8,
    activeSensors: 4,
  })

  useEffect(() => {
    const interval = setInterval(() => {
      setSensors((prev) => ({
        bmwTireTemp: {
          f: +(prev.bmwTireTemp.f + (Math.random() * 0.4 - 0.2)).toFixed(1),
          r: +(prev.bmwTireTemp.r + (Math.random() * 0.4 - 0.2)).toFixed(1),
        },
        triumphVoltage: +(
          prev.triumphVoltage +
          (Math.random() * 0.08 - 0.04)
        ).toFixed(1),
        activeSensors: Math.random() > 0.85 ? (Math.random() > 0.5 ? 5 : 3) : 4,
      }))
    }, 3000)

    return () => clearInterval(interval)
  }, [])

  useEffect(() => {
    const storedBikes = loadStoredBikes()
    if (storedBikes.length > 0) {
      setBikes(storedBikes)
      setLoading(false)
    }

    let isMounted = true

    const fetchActiveBookings = async ({ silent = false } = {}) => {
      const token = localStorage.getItem('authToken')
      if (!token) {
        if (!silent && isMounted) {
          setToast({
            message: 'Authentication token not found. Please log in.',
            type: 'error',
          })
        }
        return
      }

      try {
        const response = await fetch(
          'http://localhost:8080/api/bookings/active',
          {
            headers: {
              Accept: 'application/json',
              Authorization: `Bearer ${token}`,
            },
          },
        )

        if (!response.ok) {
          throw new Error(
            `Failed to load active bookings: ${response.statusText}`,
          )
        }

        const data = await response.json()
        if (isMounted) {
          setBikes(
            sortBikesNewestFirst((data.data || []).map(mapBookingToBike)),
          )
        }
      } catch (err) {
        if (!silent && isMounted) {
          setToast({
            message: `Error loading garage: ${err.message}`,
            type: 'error',
          })
        }
      } finally {
        if (isMounted) {
          setLoading(false)
        }
      }
    }

    fetchActiveBookings()
    const poller = setInterval(() => {
      fetchActiveBookings({ silent: true })
    }, 10000)

    return () => {
      isMounted = false
      clearInterval(poller)
    }
  }, [])

  useEffect(() => {
    try {
      localStorage.setItem(GARAGE_STORAGE_KEY, JSON.stringify(bikes))
    } catch {
      // Ignore storage write failures
    }
  }, [bikes])

  const toggleSpecs = (id) => {
    setExpandedBikeId((prev) => (prev === id ? null : id))
  }

  const handleCloseModals = () => {
    setServiceRequestStep('closed')
    setCurrentBikeInfo(null)
    setPendingBookingRequest(null)
  }

  const handleBikeInfoSubmit = (formData) => {
    setCurrentBikeInfo(formData)
    setServiceRequestStep('service_selection')
  }

  const handleBackToBikeInfo = () => {
    setPendingBookingRequest(null)
    setServiceRequestStep('bike_info')
  }

  const handleServiceSelection = async (bikeInfo, selectedServices) => {
    const normalizedServices = selectedServices.map((service) => ({
      id: service.id,
      name: service.name,
      price: Number(service.price || 0),
    }))
    const totalAmount = normalizedServices.reduce(
      (sum, service) => sum + Number(service.price || 0),
      0,
    )

    setPendingBookingRequest({
      bikeInfo,
      normalizedServices,
      totalAmount,
    })
    setServiceRequestStep('schedule')
  }

  const handleScheduleConfirm = async (scheduleInfo) => {
    const token = localStorage.getItem('authToken')
    const profile = getStoredProfile()
    if (!token) {
      setToast({
        message: 'Authentication token not found. Please log in.',
        type: 'error',
      })
      handleCloseModals()
      return
    }

    if (!pendingBookingRequest) {
      setToast({
        message: 'Booking data is missing. Please try again.',
        type: 'error',
      })
      handleCloseModals()
      return
    }

    try {
      const response = await fetch('http://localhost:8080/api/bookings', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          customer_name: getProfileDisplayName(profile),
          customer_email: profile.email,
          bike_name: pendingBookingRequest.bikeInfo.name,
          model: pendingBookingRequest.bikeInfo.model,
          plate_number: pendingBookingRequest.bikeInfo.plateNumber,
          engine_capacity: pendingBookingRequest.bikeInfo.engineCapacity,
          starts_at: scheduleInfo.startsAt,
          service_id: pendingBookingRequest.normalizedServices[0]?.id ?? null,
          service_name: pendingBookingRequest.normalizedServices
            .map((service) => service.name)
            .join(', '),
          selected_services: pendingBookingRequest.normalizedServices,
          total_amount: pendingBookingRequest.totalAmount,
          status: 'pending',
          notes:
            scheduleInfo.notes?.trim() ||
            `Services: ${pendingBookingRequest.normalizedServices.map((service) => service.name).join(', ')}`,
        }),
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(
          errorData.message ||
            `Failed to create booking: ${response.statusText}`,
        )
      }

      const newBooking = await response.json()
      setBikes((prev) =>
        sortBikesNewestFirst([mapBookingToBike(newBooking.data), ...prev]),
      )
      setToast({
        message: `${pendingBookingRequest.normalizedServices.length} service(s) added for ${pendingBookingRequest.bikeInfo.name}!`,
        type: 'success',
      })
      handleCloseModals()
    } catch (err) {
      setToast({
        message: `Error adding service: ${err.message}`,
        type: 'error',
      })
    }
  }

  const handleCancelBooking = (bikeId) => {
    const bike = bikes.find((item) => item.id === bikeId)
    if (!bike?.canCancel) {
      setToast({
        message:
          'Bookings can only be cancelled while they are pending or confirmed.',
        type: 'error',
      })
      return
    }

    setConfirmCancel({
      bikeId,
      bikeName: bike?.name,
    })
  }

  const confirmCancelBooking = async () => {
    if (!confirmCancel?.bikeId) {
      return
    }

    const token = localStorage.getItem('authToken')
    if (!token) {
      setToast({
        message: 'Authentication token not found. Please log in.',
        type: 'error',
      })
      setConfirmCancel(null)
      return
    }

    try {
      const response = await fetch(
        `http://localhost:8080/api/bookings/${confirmCancel.bikeId}/cancel`,
        {
          method: 'PUT',
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        },
      )

      const result = await response.json()
      if (!response.ok) {
        throw new Error(
          result.message || `Failed to cancel booking: ${response.statusText}`,
        )
      }

      setBikes((prev) =>
        prev.filter((bike) => bike.id !== confirmCancel.bikeId),
      )
      setExpandedBikeId((prev) => (prev === confirmCancel.bikeId ? null : prev))
      setToast({
        message: `${confirmCancel.bikeName} booking cancelled successfully.`,
        type: 'success',
      })
    } catch (err) {
      setToast({
        message: `Error cancelling booking: ${err.message}`,
        type: 'error',
      })
    } finally {
      setConfirmCancel(null)
    }
  }

  return (
    <div className="flex-grow overflow-y-auto grid-pattern p-lg">
      {toast && (
        <Toast
          message={toast.message}
          type={toast.type}
          duration={3000}
          onClose={() => setToast(null)}
        />
      )}

      <ConfirmModal
        isOpen={!!confirmCancel}
        title="Cancel Booking"
        message={`Cancel the booking for ${confirmCancel?.bikeName}? This is only allowed while the booking is pending or confirmed.`}
        confirmText="Cancel Booking"
        cancelText="Keep Booking"
        isDangerous={true}
        onConfirm={confirmCancelBooking}
        onCancel={() => setConfirmCancel(null)}
      />

      <BikeInfoModal
        isOpen={serviceRequestStep === 'bike_info'}
        onClose={handleCloseModals}
        onSubmit={handleBikeInfoSubmit}
      />

      <ServiceSelectionModal
        isOpen={serviceRequestStep === 'service_selection'}
        onClose={handleCloseModals}
        onBack={handleBackToBikeInfo}
        onServiceSelect={handleServiceSelection}
        bikeInfo={currentBikeInfo}
      />

      <BookingScheduleModal
        isOpen={serviceRequestStep === 'schedule'}
        onClose={handleCloseModals}
        onConfirm={handleScheduleConfirm}
        bikeInfo={pendingBookingRequest?.bikeInfo}
        selectedServices={pendingBookingRequest?.normalizedServices || []}
        totalAmount={pendingBookingRequest?.totalAmount || 0}
      />

      <div className="max-w-7xl mx-auto space-y-lg">
        <div className="mb-2 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
          <div>
            <h1 className="font-display-xl text-3xl md:text-4xl font-black text-primary uppercase tracking-tighter">
              LIVE WORKSHOP TRACK
            </h1>
            <p className="mt-1 max-w-[36rem] text-sm font-body-md text-on-surface-variant">
              Real workshop jobs synced from your catalog bookings. Review the
              selected services, total amount, and live progress for each
              motorcycle.
            </p>
          </div>
          <div className="flex items-center gap-2 border border-secondary/20 bg-secondary/10 px-3 py-1.5 select-none">
            <span className="w-2.5 h-2.5 rounded-full bg-secondary animate-pulse"></span>
            <span className="font-mono text-[10px] font-bold uppercase tracking-widest text-secondary">
              {sensors.activeSensors} SENSORS ACTIVE
            </span>
          </div>
        </div>

        <div className="mb-6 flex flex-wrap gap-3">
          <button
            onClick={() => setServiceRequestStep('bike_info')}
            className="border border-secondary bg-secondary px-lg py-md font-label-sm text-xs uppercase tracking-widest text-on-secondary transition-all hover:bg-primary active:scale-[0.98]"
          >
            + Add New Service
          </button>
        </div>

        {loading && (
          <div className="py-xl text-center">
            <p className="text-sm font-body-md text-on-surface-variant">
              Loading motorcycles in service...
            </p>
          </div>
        )}

        {!loading && bikes.length > 0 && (
          <div className="grid grid-cols-1 items-start gap-lg lg:grid-cols-2 animate-fadeIn">
            {bikes.map((bike) => (
              <div
                key={bike.id}
                className="group self-start border border-outline-variant bg-white shadow-sm transition-all duration-300 hover:shadow-md"
              >
                <div className="relative aspect-video overflow-hidden border-b border-outline-variant bg-surface-container-low">
                  <img
                    className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    src={bike.image}
                    alt={`${bike.name} ${bike.model}`}
                  />
                  <div className="absolute top-4 right-4 flex flex-col items-end gap-2">
                    <span
                      className={`${getStatusBadgeClass(bike.status)} px-3 py-1 font-bold text-[9px] uppercase tracking-widest`}
                    >
                      {bike.serviceStatus}
                    </span>
                  </div>
                </div>

                <div className="flex flex-grow flex-col p-md">
                  <div className="mb-6 flex items-start justify-between gap-4">
                    <div>
                      <h2 className="mb-1 font-headline-lg text-2xl font-black uppercase leading-tight text-primary">
                        {bike.name} {bike.model}
                      </h2>
                      <p className="font-mono text-[10px] uppercase tracking-widest text-outline">
                        Plate: {bike.plateNumber}
                      </p>
                      <p className="mt-1 font-mono text-[10px] uppercase tracking-widest text-secondary">
                        {bike.bookingDate
                          ? `${bike.bookingDate} | ${bike.bookingTimeLabel}`
                          : 'Schedule Pending'}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className="font-label-sm text-[9px] uppercase tracking-[0.2em] text-outline">
                        Total Amount
                      </p>
                      <p className="font-mono text-2xl font-bold text-secondary">
                        ${bike.totalAmount.toFixed(2)}
                      </p>
                    </div>
                  </div>

                  <hr className="mb-6 border-outline-variant" />

                  <div className="mb-8 grid grid-cols-3 gap-6">
                    <div className="flex flex-col">
                      <span className="mb-3 text-[9px] font-bold uppercase tracking-[0.2em] text-outline">
                        Engine Capacity
                      </span>
                      <div className="mb-2 flex items-baseline gap-1">
                        <span className="font-mono text-2xl font-bold text-primary">
                          {bike.engineCapacity}
                        </span>
                        <span className="font-mono text-[10px] text-outline">
                          CC
                        </span>
                      </div>
                    </div>

                    <div className="flex flex-col">
                      <span className="mb-3 text-[9px] font-bold uppercase tracking-[0.2em] text-outline">
                        Active Service
                      </span>
                      <div className="font-mono text-sm md:text-base font-bold text-secondary">
                        {bike.serviceType}
                      </div>
                      <div className="font-mono text-[9px] uppercase text-outline">
                        {bike.selectedServices.length} item(s)
                      </div>
                    </div>

                    <div className="flex flex-col">
                      <span className="mb-3 text-[9px] font-bold uppercase tracking-[0.2em] text-outline">
                        Assigned Lead
                      </span>
                      <div className="flex items-baseline gap-1">
                        <span className="font-mono text-sm md:text-base font-bold text-primary">
                          {bike.lead}
                        </span>
                      </div>
                      <span className="mt-1 font-mono text-[9px] uppercase text-outline">
                        LEAD TECH
                      </span>
                    </div>
                  </div>

                  <div className="mb-6 border border-secondary/20 bg-secondary/[0.02] p-3">
                    <div className="mb-3 flex justify-between items-center">
                      <span className="font-bold text-[8px] uppercase tracking-[0.2em] text-secondary">
                        Catalog Services
                      </span>
                      <span className="font-mono text-[10px] font-bold uppercase tracking-widest text-secondary">
                        ${bike.totalAmount.toFixed(2)}
                      </span>
                    </div>
                    <div className="space-y-2">
                      {bike.selectedServices.map((service) => (
                        <div
                          key={service.id}
                          className="flex items-center justify-between border-b border-outline-variant/70 pb-2 last:border-b-0 last:pb-0"
                        >
                          <span className="text-[11px] font-bold uppercase tracking-wide text-primary">
                            {service.name}
                          </span>
                          <span className="font-mono text-[11px] font-bold text-secondary">
                            ${Number(service.price || 0).toFixed(2)}
                          </span>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div
                    className={`mb-6 border p-3 ${getStatusPanelClass(bike.status)}`}
                  >
                    <div className="mb-1 flex justify-between items-center">
                      <span className="font-bold text-[8px] uppercase tracking-[0.2em]">
                        Workshop Progress
                      </span>
                      <span className="font-mono text-[10px] font-bold uppercase tracking-widest">
                        {bike.serviceStatus}
                      </span>
                    </div>
                    <div className="mt-2 font-mono text-[8px] uppercase text-outline">
                      Milestone: {bike.milestone}
                    </div>
                  </div>

                  {expandedBikeId === bike.id && (
                    <div className="mb-6 animate-fadeIn space-y-3 border border-outline-variant bg-surface-container-low p-4 font-mono text-xs">
                      <p className="mb-1 text-[10px] font-bold uppercase tracking-wider text-primary">
                        Booking Breakdown
                      </p>
                      <div className="flex justify-between border-b border-outline-variant pb-1">
                        <span className="uppercase text-on-surface-variant">
                          Booking Ref
                        </span>
                        <span className="font-bold text-primary">
                          #{bike.bookingId}
                        </span>
                      </div>
                      <div className="flex justify-between border-b border-outline-variant pb-1">
                        <span className="uppercase text-on-surface-variant">
                          Status
                        </span>
                        <span className="font-bold text-primary">
                          {bike.serviceStatus}
                        </span>
                      </div>
                      <div className="flex justify-between border-b border-outline-variant pb-1">
                        <span className="uppercase text-on-surface-variant">
                          Items
                        </span>
                        <span className="font-bold text-primary">
                          {bike.selectedServices.length}
                        </span>
                      </div>
                      <div className="flex justify-between border-b border-outline-variant pb-1">
                        <span className="uppercase text-on-surface-variant">
                          Total
                        </span>
                        <span className="font-bold text-primary">
                          ${bike.totalAmount.toFixed(2)}
                        </span>
                      </div>
                      <div className="grid grid-cols-1 gap-2 border-b border-outline-variant pb-2 md:grid-cols-2">
                        <div className="flex justify-between gap-3">
                          <span className="uppercase text-on-surface-variant">
                            Booking Date
                          </span>
                          <span className="font-bold text-primary text-right">
                            {bike.bookingDate || 'N/A'}
                          </span>
                        </div>
                        <div className="flex justify-between gap-3">
                          <span className="uppercase text-on-surface-variant">
                            Booking Time
                          </span>
                          <span className="font-bold text-primary text-right">
                            {bike.bookingTimeLabel}
                          </span>
                        </div>
                        <div className="flex justify-between gap-3">
                          <span className="uppercase text-on-surface-variant">
                            Brand
                          </span>
                          <span className="font-bold text-primary text-right">
                            {bike.name}
                          </span>
                        </div>
                        <div className="flex justify-between gap-3">
                          <span className="uppercase text-on-surface-variant">
                            Model
                          </span>
                          <span className="font-bold text-primary text-right">
                            {bike.model || 'N/A'}
                          </span>
                        </div>
                        <div className="flex justify-between gap-3">
                          <span className="uppercase text-on-surface-variant">
                            Plate Number
                          </span>
                          <span className="font-bold text-primary text-right">
                            {bike.plateNumber || 'N/A'}
                          </span>
                        </div>
                        <div className="flex justify-between gap-3">
                          <span className="uppercase text-on-surface-variant">
                            Engine Capacity
                          </span>
                          <span className="font-bold text-primary text-right">
                            {bike.engineCapacity}
                          </span>
                        </div>
                        <div className="flex justify-between gap-3">
                          <span className="uppercase text-on-surface-variant">
                            Customer
                          </span>
                          <span className="font-bold text-primary text-right">
                            {bike.customerName}
                          </span>
                        </div>
                        <div className="flex justify-between gap-3">
                          <span className="uppercase text-on-surface-variant">
                            Email
                          </span>
                          <span className="font-bold text-primary text-right break-all">
                            {bike.customerEmail}
                          </span>
                        </div>
                      </div>
                      {bike.selectedServices.length > 0 && (
                        <div className="pt-1">
                          <span className="block uppercase text-on-surface-variant mb-2">
                            Selected Services
                          </span>
                          <div className="space-y-2">
                            {bike.selectedServices.map((service) => (
                              <div
                                key={service.id}
                                className="flex items-center justify-between border-b border-outline-variant pb-1 last:border-b-0 last:pb-0"
                              >
                                <span className="text-primary">
                                  {service.name}
                                </span>
                                <span className="font-bold text-primary">
                                  ${Number(service.price || 0).toFixed(2)}
                                </span>
                              </div>
                            ))}
                          </div>
                        </div>
                      )}
                      {bike.notes && (
                        <div className="pt-1">
                          <span className="block uppercase text-on-surface-variant mb-1">
                            Description
                          </span>
                          <span className="leading-relaxed text-primary">
                            {bike.notes}
                          </span>
                        </div>
                      )}
                    </div>
                  )}

                  <div className="mt-auto flex gap-4">
                    <button
                      onClick={() => toggleSpecs(bike.id)}
                      className="flex-1 border border-primary py-3.5 text-[10px] font-bold uppercase tracking-widest text-primary transition-all hover:bg-primary hover:text-white active:scale-[0.98]"
                    >
                      {expandedBikeId === bike.id
                        ? 'Hide Breakdown'
                        : 'View Breakdown'}
                    </button>
                    {bike.canCancel ? (
                      <button
                        onClick={() => handleCancelBooking(bike.id)}
                        className="border border-error px-md py-3.5 text-[10px] font-bold uppercase tracking-widest text-error transition-all hover:bg-error hover:text-white active:scale-[0.98]"
                      >
                        Cancel Booking
                      </button>
                    ) : (
                      <button
                        disabled
                        className="cursor-not-allowed border border-outline-variant px-md py-3.5 text-[10px] font-bold uppercase tracking-widest text-outline opacity-60"
                      >
                        Status Locked
                      </button>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {!loading && bikes.length === 0 && (
          <div className="py-xl text-center">
            <p className="mb-lg text-sm font-body-md text-on-surface-variant">
              No motorcycles currently in service.
            </p>
            <button
              onClick={() => setServiceRequestStep('bike_info')}
              className="bg-primary px-lg py-md font-label-sm text-xs uppercase tracking-widest text-on-primary transition-all hover:bg-secondary active:scale-95"
            >
              + Add New Service
            </button>
          </div>
        )}
      </div>
    </div>
  )
}
