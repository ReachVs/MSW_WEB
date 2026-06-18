import { useState, useEffect } from 'react'
import BookingScheduleModal from './BookingScheduleModal'
import {
  getProfileDisplayName,
  getStoredProfile,
} from '../utils/profileStorage'

// Icons mapping using Material Symbols
const iconMap = {
  build: 'build',
  wash: 'wash',
  monitor_heart: 'monitor_heart',
  speed: 'speed',
  opacity: 'opacity',
  slow_motion_video: 'slow_motion_video',
  settings: 'settings',
  airline_seat_recline_normal: 'airline_seat_recline_normal',
  tire_repair: 'tire_repair',
  settings_input_component: 'settings_input_component',
  battery_charging_full: 'battery_charging_full',
  light_mode: 'light_mode',
  electric_bolt: 'electric_bolt',
  handyman: 'handyman',
  verified: 'verified',
  bolt: 'bolt',
  flash_on: 'flash_on',
  air: 'air',
  ev_station: 'ev_station',
  favorite: 'favorite',
  shield: 'shield',
  check_circle: 'check_circle',
}

export default function CatalogPage({ onBook }) {
  const [catalogData, setCatalogData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [expandedMain, setExpandedMain] = useState(null)
  const [expandedSub, setExpandedSub] = useState({})
  const [selectedServices, setSelectedServices] = useState([])
  const [showSummary, setShowSummary] = useState(false)
  const [showBikeForm, setShowBikeForm] = useState(false)
  const [showSchedule, setShowSchedule] = useState(false)
  const [bikeInfo, setBikeInfo] = useState({
    name: '',
    model: '',
    plateNumber: '',
    engineCapacity: '',
  })

  useEffect(() => {
    const fetchCatalog = async () => {
      try {
        const response = await fetch('http://localhost:8080/api/services')
        if (response.ok) {
          const data = await response.json()
          setCatalogData(data.data)
        } else {
          throw new Error('Failed to fetch catalog')
        }
      } catch (err) {
        setError(err.message)
      } finally {
        setLoading(false)
      }
    }
    fetchCatalog()
  }, [])

  const toggleMainCategory = (mainKey) => {
    setExpandedMain(expandedMain === mainKey ? null : mainKey)
    setExpandedSub({})
  }

  const toggleSubCategory = (mainKey, subKey) => {
    const key = `${mainKey}-${subKey}`
    setExpandedSub((prev) => (prev[key] ? {} : { [key]: true }))
  }

  const toggleService = (service) => {
    if (service.selection_mode === 0) return // Can't select headers

    setSelectedServices((prev) => {
      const exists = prev.find((s) => s.id === service.id)
      if (exists) {
        return prev.filter((s) => s.id !== service.id)
      } else {
        let filtered = prev
        if (expandedMain === 'washing') {
          filtered = prev.filter((s) => s.category !== 'washing')
        }

        const serviceWithCategory = {
          ...service,
          category: expandedMain,
        }
        return [...filtered, serviceWithCategory]
      }
    })
  }

  const isServiceSelected = (serviceId) => {
    return selectedServices.find((s) => s.id === serviceId)
  }

  const removeSelectedService = (serviceId) => {
    setSelectedServices((prev) =>
      prev.filter((service) => service.id !== serviceId),
    )
  }

  const calculateTotal = () => {
    return selectedServices.reduce(
      (sum, s) => sum + (parseFloat(s.price) || 0),
      0,
    )
  }

  const handleConfirmServices = () => {
    if (selectedServices.length === 0) {
      alert('Please select at least one service')
      return
    }
    setShowSummary(true)
  }

  const handleProceedToBikeForm = () => {
    setShowSummary(false)
    setShowBikeForm(true)
  }

  const handleOpenSchedule = () => {
    if (
      !bikeInfo.name.trim() ||
      !bikeInfo.model.trim() ||
      !bikeInfo.plateNumber.trim()
    ) {
      alert('Please fill in all bike information')
      return
    }

    setShowSchedule(true)
  }

  const handleSubmitBooking = async (scheduleInfo) => {
    if (!scheduleInfo?.startsAt) {
      alert('Please select an available date and time slot')
      return
    }

    const token = localStorage.getItem('authToken')
    const profile = getStoredProfile()
    if (!token) {
      alert('Please log in to create a booking')
      return
    }

    try {
      // Create booking with all selected services
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
          bike_name: bikeInfo.name,
          model: bikeInfo.model,
          plate_number: bikeInfo.plateNumber,
          engine_capacity: bikeInfo.engineCapacity,
          starts_at: scheduleInfo.startsAt,
          service_name: selectedServices.map((s) => s.name).join(', '),
          selected_services: selectedServices.map((s) => ({
            id: s.id,
            name: s.name,
            price: parseFloat(s.price) || 0,
          })),
          total_amount: calculateTotal(),
          status: 'pending',
          notes:
            scheduleInfo.notes?.trim() ||
            `Services: ${selectedServices.map((s) => s.name).join(', ')}`,
        }),
      })

      if (!response.ok) {
        throw new Error('Failed to create booking')
      }

      const result = await response.json()

      // Reset state
      setShowSummary(false)
      setShowBikeForm(false)
      setShowSchedule(false)
      setSelectedServices([])
      setBikeInfo({ name: '', model: '', plateNumber: '', engineCapacity: '' })
      setExpandedMain(null)
      setExpandedSub({})

      if (onBook) {
        onBook(result.data)
      }
    } catch (err) {
      alert(`Error creating booking: ${err.message}`)
    }
  }

  const handleBack = () => {
    if (showBikeForm) {
      setShowSchedule(false)
      setShowBikeForm(false)
    } else if (showSummary) {
      setShowSummary(false)
    }
  }

  if (loading) {
    return (
      <div className="p-lg w-full flex items-center justify-center min-h-screen">
        <p className="text-primary font-body-md">Loading catalog...</p>
      </div>
    )
  }

  if (error) {
    return (
      <div className="p-lg w-full flex items-center justify-center min-h-screen">
        <p className="text-error font-body-md">Error: {error}</p>
      </div>
    )
  }

  // Summary View
  if (showSummary) {
    return (
      <div className="p-lg w-full max-w-4xl mx-auto">
        <div className="mb-xl border-b border-outline-variant pb-md">
          <h1 className="font-display-xl text-4xl text-primary mb-xs uppercase tracking-tighter">
            Service Summary
          </h1>
          <p className="text-on-surface-variant font-body-sm">
            Review your selected services
          </p>
        </div>

        <div className="bg-white border border-outline-variant p-lg mb-lg">
          <h2 className="font-label-sm text-xs uppercase text-outline mb-md">
            Selected Services
          </h2>
          <div className="space-y-md mb-lg">
            {selectedServices.map((service) => (
              <div
                key={service.id}
                className="flex justify-between items-start border-b border-outline-variant pb-md last:border-b-0"
              >
                <div>
                  <p className="font-bold text-sm uppercase">{service.name}</p>
                  <p className="text-xs text-outline">{service.description}</p>
                </div>
                <div className="flex items-start gap-sm">
                  <span className="mono-data text-secondary font-bold">
                    {service.price > 0
                      ? `$${parseFloat(service.price).toFixed(2)}`
                      : 'FREE'}
                  </span>
                  <button
                    type="button"
                    onClick={() => removeSelectedService(service.id)}
                    className="border border-error px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-error transition-colors hover:bg-error hover:text-white"
                  >
                    Remove
                  </button>
                </div>
              </div>
            ))}
          </div>
          <div className="border-t border-outline-variant pt-md flex justify-between items-baseline">
            <span className="font-bold uppercase">Total</span>
            <span className="mono-data text-2xl font-bold text-primary">
              ${calculateTotal().toFixed(2)}
            </span>
          </div>
        </div>

        <div className="flex gap-md">
          <button
            onClick={handleBack}
            className="flex-1 border border-outline-variant py-md font-bold uppercase text-sm hover:bg-surface-container-low transition-colors"
          >
            Back
          </button>
          <button
            onClick={handleProceedToBikeForm}
            className="flex-1 bg-primary text-white py-md font-bold uppercase text-sm hover:bg-primary/90 transition-colors"
          >
            Proceed to Bike Info
          </button>
        </div>
      </div>
    )
  }

  // Bike Info Form
  if (showBikeForm) {
    return (
      <div className="p-lg w-full max-w-4xl mx-auto">
        <BookingScheduleModal
          key={showSchedule ? 'schedule-open' : 'schedule-closed'}
          isOpen={showSchedule}
          onClose={() => setShowSchedule(false)}
          onConfirm={handleSubmitBooking}
          bikeInfo={bikeInfo}
          selectedServices={selectedServices}
          totalAmount={calculateTotal()}
        />
        <div className="mb-xl border-b border-outline-variant pb-md">
          <h1 className="font-display-xl text-4xl text-primary mb-xs uppercase tracking-tighter">
            Bike Information
          </h1>
          <p className="text-on-surface-variant font-body-sm">
            Enter your motorcycle details
          </p>
        </div>

        <div className="bg-white border border-outline-variant p-lg">
          <div className="grid grid-cols-2 gap-6 mb-6">
            <div>
              <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-3">
                Brand
              </label>
              <input
                type="text"
                value={bikeInfo.name}
                onChange={(e) =>
                  setBikeInfo({ ...bikeInfo, name: e.target.value })
                }
                className="w-full border border-outline-variant px-md py-3 font-body-md text-sm focus:outline-none focus:border-secondary transition-colors"
                placeholder="e.g., DUCATI"
              />
            </div>
            <div>
              <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-3">
                Model
              </label>
              <input
                type="text"
                value={bikeInfo.model}
                onChange={(e) =>
                  setBikeInfo({ ...bikeInfo, model: e.target.value })
                }
                className="w-full border border-outline-variant px-md py-3 font-body-md text-sm focus:outline-none focus:border-secondary transition-colors"
                placeholder="e.g., PANIGALE V4 S"
              />
            </div>
          </div>

          <div className="mb-6">
            <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-3">
              Plate Number
            </label>
            <input
              type="text"
              value={bikeInfo.plateNumber}
              onChange={(e) =>
                setBikeInfo({ ...bikeInfo, plateNumber: e.target.value })
              }
              className="w-full border border-outline-variant px-md py-3 font-mono text-sm focus:outline-none focus:border-secondary transition-colors"
              placeholder="e.g., 1A-1234"
            />
          </div>

          <div className="mb-6">
            <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-3">
              Engine Capacity (CC)
            </label>
            <input
              type="text"
              value={bikeInfo.engineCapacity}
              onChange={(e) =>
                setBikeInfo({ ...bikeInfo, engineCapacity: e.target.value })
              }
              className="w-full border border-outline-variant px-md py-3 font-body-md text-sm focus:outline-none focus:border-secondary transition-colors"
              placeholder="e.g., 1100"
            />
          </div>

          <div className="border-t border-outline-variant pt-md flex justify-between items-baseline mb-6">
            <span className="font-bold uppercase">Total</span>
            <span className="mono-data text-2xl font-bold text-primary">
              ${calculateTotal().toFixed(2)}
            </span>
          </div>

          <div className="flex gap-md">
            <button
              onClick={handleBack}
              className="flex-1 border border-outline-variant py-md font-bold uppercase text-sm hover:bg-surface-container-low transition-colors"
            >
              Back
            </button>
            <button
              onClick={handleOpenSchedule}
              className="flex-1 bg-primary text-white py-md font-bold uppercase text-sm hover:bg-primary/90 transition-colors"
            >
              Select Date & Time
            </button>
          </div>
        </div>
      </div>
    )
  }

  // Main Catalog View
  const mainCategories = [
    {
      key: 'maintenance',
      name: 'Maintenance Services',
      icon: 'build',
      image: '/mechanic-chain.png',
      description:
        'Chains, fluids, brakes, and safety inspection. Keep your machine operating within factory safety tolerances.',
    },
    {
      key: 'washing',
      name: 'Washing Services',
      icon: 'wash',
      image: '/motorcycle-wash.png',
      description:
        'Full exterior detail, foam wash, tire cleaning, and protective treatments to keep surfaces pristine.',
    },
    {
      key: 'engine_checkup',
      name: 'Engine Check Up',
      icon: 'monitor_heart',
      image: '/mechanic-diagnostic.png',
      description:
        'Diagnostics, compression tests, spark plugs, and injector analysis for peak engine health.',
    },
    {
      key: 'tuning',
      name: 'Tuning Performance',
      icon: 'speed',
      image: '/dyno-tuning.jpg',
      description:
        'Dyno analysis, ECU optimization, remaps, and speed calibrations for performance racing.',
    },
  ]

  return (
    <div className="p-lg w-full">
      <div className="max-w-[1400px] mx-auto">
        <div className="mb-xl border-b border-outline-variant pb-md">
          <div className="flex flex-col gap-lg lg:flex-row lg:items-start lg:justify-between">
            <div className="flex-1 min-w-0">
              <h1 className="font-display-xl text-4xl md:text-7xl text-primary mb-xs uppercase tracking-tighter">
                Technical Catalog
              </h1>
              <div className="flex flex-col md:flex-row md:items-center gap-md">
                <span className="font-label-sm text-xs text-secondary uppercase bg-secondary-fixed px-2 py-1 w-fit">
                  Standard Operating Procedure v2.4
                </span>
                <div className="h-px flex-1 bg-outline-variant"></div>
                <span className="mono-data text-xs text-outline">
                  REF: MA-SRV-2026-001
                </span>
              </div>
            </div>

            {selectedServices.length > 0 && (
              <div className="w-full lg:w-[280px] shrink-0 border border-outline-variant bg-white p-md">
                <p className="font-label-sm text-xs uppercase text-outline mb-1">
                  {selectedServices.length} service(s) selected
                </p>
                <div className="mb-md max-h-40 space-y-2 overflow-y-auto pr-1">
                  {selectedServices.map((service) => (
                    <div
                      key={service.id}
                      className="flex items-start justify-between gap-2 border-b border-outline-variant/70 pb-2 last:border-b-0 last:pb-0"
                    >
                      <div className="min-w-0">
                        <p className="truncate text-[11px] font-bold uppercase text-primary">
                          {service.name}
                        </p>
                        <p className="mono-data text-[11px] text-secondary">
                          ${Number(service.price || 0).toFixed(2)}
                        </p>
                      </div>
                      <button
                        type="button"
                        onClick={() => removeSelectedService(service.id)}
                        className="border border-error px-2 py-1 text-[9px] font-bold uppercase tracking-widest text-error transition-colors hover:bg-error hover:text-white"
                      >
                        Remove
                      </button>
                    </div>
                  ))}
                </div>
                <p className="mono-data text-2xl font-bold text-primary mb-md">
                  ${calculateTotal().toFixed(2)}
                </p>
                <button
                  onClick={handleConfirmServices}
                  className="w-full bg-primary text-white py-md font-bold uppercase text-sm hover:bg-primary/90 transition-colors"
                >
                  Review & Continue
                </button>
              </div>
            )}
          </div>
        </div>

        {/* 4 Main Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-lg mb-xl">
          {mainCategories.map((cat) => {
            const isActive = expandedMain === cat.key

            return (
              <button
                key={cat.key}
                onClick={() => toggleMainCategory(cat.key)}
                className={`group flex flex-col text-left border-2 transition-all duration-300 w-full overflow-hidden ${
                  isActive
                    ? 'border-primary bg-primary-container'
                    : 'border-outline-variant hover:border-secondary bg-white'
                }`}
              >
                {/* Card Visual Header Image */}
                <div className="relative aspect-video w-full overflow-hidden bg-surface-container-low border-b border-outline-variant">
                  <img
                    src={cat.image}
                    alt={cat.name}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  {/* Category Indicator Icon Overlay */}
                  <div className="absolute bottom-3 right-3 bg-white p-2 border border-outline-variant shadow-sm flex items-center justify-center">
                    <span className="material-symbols-outlined text-secondary text-2xl">
                      {iconMap[cat.icon] || 'list_alt'}
                    </span>
                  </div>
                </div>

                {/* Card Info Content */}
                <div className="flex-grow p-sm flex flex-col gap-sm">
                  <div>
                    <h3
                      className={`font-headline-lg text-lg font-black uppercase tracking-tight ${
                        isActive ? 'text-white' : 'text-primary'
                      }`}
                    >
                      {cat.name}
                    </h3>
                    <p
                      className={`text-xs mt-1 leading-relaxed ${
                        isActive ? 'text-white/80' : 'text-on-surface-variant'
                      }`}
                    >
                      {cat.description}
                    </p>
                  </div>

                  <div className="mt-auto pt-sm border-t border-outline-variant/30 flex justify-between items-center w-full">
                    <span
                      className={`font-mono text-[10px] font-bold uppercase tracking-wider ${
                        isActive ? 'text-white/70' : 'text-outline'
                      }`}
                    >
                      {catalogData[cat.key]?.subcategories
                        ? Object.keys(catalogData[cat.key].subcategories).length
                        : 0}{' '}
                      subcategories
                    </span>
                    <span className="material-symbols-outlined text-secondary text-sm group-hover:translate-x-1 transition-transform">
                      arrow_right_alt
                    </span>
                  </div>
                </div>
              </button>
            )
          })}
        </div>

        {/* Expanded Content */}
        {expandedMain && catalogData[expandedMain] && (
          <div className="space-y-lg">
            <div className="flex items-center gap-md mb-md">
              <button
                onClick={() => setExpandedMain(null)}
                className="material-symbols-outlined text-outline hover:text-primary transition-colors"
              >
                arrow_back
              </button>
              <h2 className="font-headline-lg text-2xl uppercase font-bold">
                {catalogData[expandedMain].name}
              </h2>
            </div>

            <div className="flex flex-col gap-lg">
              {Object.entries(catalogData[expandedMain].subcategories || {})
                .filter(
                  ([subKey]) =>
                    subKey !== '_root' &&
                    subKey !== 'General' &&
                    subKey !== 'General Maintenance',
                ) // Filter out root/general subcategories
                .map(([subKey, subData]) => {
                  const isExpanded = expandedSub[`${expandedMain}-${subKey}`]
                  const selectableItems = subData.items.filter(
                    (item) => item.selection_mode === 1,
                  )

                  // Skip subcategories with no selectable items
                  if (selectableItems.length === 0) return null

                  const isWashing = expandedMain === 'washing'
                  const headerItem =
                    subData.items.find((item) => item.selection_mode === 0) ||
                    subData.items[0]
                  const packagePrice = subData.items
                    .filter((item) => item.selection_mode === 1)
                    .reduce(
                      (sum, item) => sum + (parseFloat(item.price) || 0),
                      0,
                    )

                  const packageService = {
                    id: headerItem.id,
                    name: subData.name,
                    price: packagePrice,
                    description: headerItem.description || subData.name,
                    selection_mode: 1,
                  }

                  if (isWashing) {
                    return (
                      <div
                        key={subKey}
                        className={`w-full overflow-hidden border transition-colors ${
                          isServiceSelected(headerItem.id)
                            ? 'border-secondary bg-secondary/[0.06]'
                            : 'border-outline-variant bg-white hover:border-secondary'
                        }`}
                      >
                        <div className="w-full p-md flex items-center justify-between">
                          <div className="flex items-center gap-md">
                            <input
                              type="checkbox"
                              id={`checkbox-${headerItem.id}`}
                              checked={!!isServiceSelected(headerItem.id)}
                              onChange={() => toggleService(packageService)}
                              className="w-5 h-5 text-secondary border-outline-variant cursor-pointer"
                            />
                            <label
                              htmlFor={`checkbox-${headerItem.id}`}
                              className="font-bold uppercase cursor-pointer text-primary"
                            >
                              {subData.name}
                            </label>
                          </div>

                          <div className="flex items-center gap-md">
                            <span className="mono-data text-secondary font-bold">
                              ${packagePrice.toFixed(2)}
                            </span>
                            <button
                              type="button"
                              onClick={() =>
                                toggleSubCategory(expandedMain, subKey)
                              }
                              className="flex items-center justify-center p-xs hover:bg-surface-container-high transition-colors"
                            >
                              <span className="font-label-sm text-[10px] uppercase text-outline mr-xs tracking-wider">
                                {isExpanded ? 'Hide Details' : 'Show Details'}
                              </span>
                              <span
                                className="material-symbols-outlined text-outline transition-transform"
                                style={{
                                  transform: isExpanded
                                    ? 'rotate(180deg)'
                                    : 'none',
                                }}
                              >
                                expand_more
                              </span>
                            </button>
                          </div>
                        </div>

                        {isExpanded && (
                          <div className="border-t border-outline-variant p-md bg-surface-container-low/50 space-y-md">
                            <div>
                              <p className="font-label-sm text-[9px] uppercase tracking-widest text-outline mb-sm font-bold">
                                Included Package Services:
                              </p>
                              <div className="grid grid-cols-1 md:grid-cols-2 gap-sm">
                                {selectableItems.map((item) => (
                                  <div
                                    key={item.id}
                                    className="flex items-start gap-2"
                                  >
                                    <span className="material-symbols-outlined text-green-600 text-sm mt-0.5 select-none">
                                      check_circle
                                    </span>
                                    <div>
                                      <p className="text-xs font-bold uppercase text-primary">
                                        {item.name}
                                      </p>
                                      <p className="text-[10px] text-on-surface-variant">
                                        {item.description}
                                      </p>
                                    </div>
                                  </div>
                                ))}
                              </div>
                            </div>
                          </div>
                        )}
                      </div>
                    )
                  }

                  // Non-washing categories remain with their original rendering
                  return (
                    <div
                      key={subKey}
                      className="w-full overflow-hidden border border-outline-variant bg-white"
                    >
                      <button
                        onClick={() => toggleSubCategory(expandedMain, subKey)}
                        className="w-full p-md flex justify-between items-center hover:bg-surface-container-low transition-colors"
                      >
                        <div className="flex items-center gap-md">
                          <span className="material-symbols-outlined text-secondary text-2xl">
                            {selectableItems[0]?.icon || 'expand_more'}
                          </span>
                          <span className="font-bold uppercase">
                            {subData.name}
                          </span>
                        </div>
                        <div className="flex items-center gap-md">
                          <span className="mono-data text-xs text-outline">
                            {selectableItems.length} options
                          </span>
                          <span
                            className="material-symbols-outlined text-outline transition-transform"
                            style={{
                              transform: isExpanded ? 'rotate(180deg)' : 'none',
                            }}
                          >
                            expand_more
                          </span>
                        </div>
                      </button>

                      {isExpanded && (
                        <div className="border-t border-outline-variant p-md space-y-sm">
                          {selectableItems.map((item) => (
                            <label
                              key={item.id}
                              className={`flex items-start gap-md p-sm border border-outline-variant cursor-pointer transition-colors ${
                                isServiceSelected(item.id)
                                  ? 'border-primary bg-primary-container/30'
                                  : 'hover:border-secondary'
                              }`}
                            >
                              <input
                                type="checkbox"
                                checked={!!isServiceSelected(item.id)}
                                onChange={() => toggleService(item)}
                                className="mt-1"
                              />
                              <div className="flex-1">
                                <div className="flex justify-between items-start">
                                  <p className="font-bold text-sm uppercase">
                                    {item.name}
                                  </p>
                                  <span className="mono-data text-secondary font-bold ml-2">
                                    {item.price > 0
                                      ? `$${parseFloat(item.price).toFixed(2)}`
                                      : 'FREE'}
                                  </span>
                                </div>
                                <p className="text-xs text-outline mt-1">
                                  {item.description}
                                </p>
                              </div>
                            </label>
                          ))}
                        </div>
                      )}
                    </div>
                  )
                })}
            </div>
          </div>
        )}

        {!expandedMain && (
          <div className="text-center py-xl text-outline">
            <p className="font-body-md">
              Select a category above to view available services
            </p>
          </div>
        )}
      </div>
    </div>
  )
}
