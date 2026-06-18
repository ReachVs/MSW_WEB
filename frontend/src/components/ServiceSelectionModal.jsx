import { useEffect, useState } from 'react'

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

function getIcon(iconName, className = 'text-primary') {
  return (
    <span className={`material-symbols-outlined text-3xl ${className}`}>
      {iconMap[iconName] || 'list_alt'}
    </span>
  )
}

const mainCategories = [
  { key: 'maintenance', name: 'Maintenance Services', icon: 'build' },
  { key: 'washing', name: 'Washing Services', icon: 'wash' },
  { key: 'engine_checkup', name: 'Engine Check Up', icon: 'monitor_heart' },
  { key: 'tuning', name: 'Tuning Performance', icon: 'speed' },
]

export default function ServiceSelectionModal({
  isOpen,
  onClose,
  onBack,
  onServiceSelect,
  bikeInfo,
  initialSelectedServices = [],
}) {
  const [catalogData, setCatalogData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [expandedMain, setExpandedMain] = useState(null)
  const [expandedSub, setExpandedSub] = useState({})
  const [selectedServices, setSelectedServices] = useState(
    () => initialSelectedServices,
  )

  useEffect(() => {
    if (!isOpen) return

    const fetchServices = async () => {
      setLoading(true)
      setError(null)
      try {
        const response = await fetch('http://localhost:8080/api/services', {
          headers: {
            Accept: 'application/json',
          },
        })

        if (!response.ok) {
          throw new Error(`Failed to fetch services: ${response.statusText}`)
        }

        const data = await response.json()
        setCatalogData(data.data)
        setExpandedMain(null)
        setExpandedSub({})
      } catch (err) {
        setError(err.message)
      } finally {
        setLoading(false)
      }
    }

    fetchServices()
  }, [isOpen])

  const toggleMainCategory = (mainKey) => {
    setExpandedMain((prev) => (prev === mainKey ? null : mainKey))
    setExpandedSub({})
  }

  const toggleSubCategory = (mainKey, subKey) => {
    const key = `${mainKey}-${subKey}`
    setExpandedSub((prev) => (prev[key] ? {} : { [key]: true }))
  }

  const toggleService = (service) => {
    setSelectedServices((prev) => {
      const exists = prev.some((item) => item.id === service.id)
      if (exists) {
        return prev.filter((item) => item.id !== service.id)
      }

      return [...prev, { ...service, price: Number(service.price || 0) }]
    })
  }

  const isServiceSelected = (serviceId) =>
    selectedServices.some((service) => service.id === serviceId)

  const calculateTotal = () =>
    selectedServices.reduce(
      (sum, service) => sum + Number(service.price || 0),
      0,
    )

  const handleSubmit = () => {
    if (selectedServices.length === 0) {
      alert('Please select at least one service.')
      return
    }

    onServiceSelect(bikeInfo, selectedServices)
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-md animate-fadeIn">
      <div className="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden border border-outline-variant bg-surface-container-lowest shadow-xl">
        <div className="shrink-0 border-b border-outline-variant p-lg">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <h2 className="font-headline-md text-lg font-bold uppercase tracking-tight text-primary">
                Select Service for {bikeInfo?.name} {bikeInfo?.model}
              </h2>
              {bikeInfo && (
                <p className="mt-1 text-sm font-body-sm text-on-surface-variant">
                  Plate: {bikeInfo.plateNumber} | Engine Capacity:{' '}
                  {bikeInfo.engineCapacity} CC
                </p>
              )}
            </div>

            {selectedServices.length > 0 && (
              <div className="w-full shrink-0 border border-outline-variant bg-white p-md lg:w-[260px]">
                <p className="mb-1 text-xs font-label-sm uppercase text-outline">
                  {selectedServices.length} service(s) selected
                </p>
                <p className="mono-data mb-md text-2xl font-bold text-primary">
                  ${calculateTotal().toFixed(2)}
                </p>
                <button
                  type="button"
                  onClick={handleSubmit}
                  className="w-full bg-primary py-md text-xs font-label-sm uppercase tracking-widest text-on-primary transition-all hover:bg-secondary"
                >
                  Confirm Services
                </button>
              </div>
            )}
          </div>
        </div>

        <div className="min-h-0 flex-1 overflow-y-auto overscroll-contain p-lg">
          {loading && (
            <p className="py-md text-center font-body-md text-primary">
              Loading services...
            </p>
          )}
          {error && (
            <p className="py-md text-center font-body-md text-error">
              Error: {error}
            </p>
          )}

          {!loading && !error && catalogData && (
            <div className="space-y-xl">
              <div className="grid grid-cols-1 gap-lg md:grid-cols-2 xl:grid-cols-4">
                {mainCategories.map((category) => {
                  const isActive = expandedMain === category.key
                  return (
                    <button
                      key={category.key}
                      type="button"
                      onClick={() => toggleMainCategory(category.key)}
                      className={`border-2 p-lg text-left transition-all ${
                        isActive
                          ? 'border-primary bg-primary-container'
                          : 'border-outline-variant bg-white hover:border-secondary'
                      }`}
                    >
                      <div className="mb-md">
                        {getIcon(
                          category.icon,
                          isActive ? 'text-white' : 'text-primary',
                        )}
                      </div>
                      <h3
                        className={`text-xl font-headline-md font-bold uppercase ${isActive ? 'text-white' : 'text-primary'}`}
                      >
                        {category.name}
                      </h3>
                      <p
                        className={`mt-1 text-xs ${isActive ? 'text-white/80' : 'text-outline'}`}
                      >
                        {catalogData[category.key]?.subcategories
                          ? Object.keys(catalogData[category.key].subcategories)
                              .length
                          : 0}{' '}
                        categories
                      </p>
                    </button>
                  )
                })}
              </div>

              {expandedMain && catalogData[expandedMain] && (
                <div className="space-y-lg">
                  <div className="flex items-center gap-md">
                    <button
                      type="button"
                      onClick={() => setExpandedMain(null)}
                      className="material-symbols-outlined text-outline transition-colors hover:text-primary"
                    >
                      arrow_back
                    </button>
                    <h3 className="text-2xl font-headline-lg font-bold uppercase text-primary">
                      {catalogData[expandedMain].name}
                    </h3>
                  </div>

                  <div className="flex flex-col gap-lg">
                    {Object.entries(
                      catalogData[expandedMain].subcategories || {},
                    )
                      .filter(
                        ([subKey]) =>
                          subKey !== '_root' &&
                          subKey !== 'General' &&
                          subKey !== 'General Maintenance',
                      )
                      .map(([subKey, subData]) => {
                        const expandKey = `${expandedMain}-${subKey}`
                        const isExpanded = !!expandedSub[expandKey]
                        const selectableItems = (subData.items || []).filter(
                          (item) => item.selection_mode === 1,
                        )

                        if (selectableItems.length === 0) return null

                        return (
                          <div
                            key={subKey}
                            className="overflow-hidden border border-outline-variant bg-white"
                          >
                            <button
                              type="button"
                              onClick={() =>
                                toggleSubCategory(expandedMain, subKey)
                              }
                              className="flex w-full items-center justify-between p-md transition-colors hover:bg-surface-container-low"
                            >
                              <div className="flex items-center gap-md">
                                <span className="material-symbols-outlined text-2xl text-secondary">
                                  {selectableItems[0]?.icon || 'list_alt'}
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
                                    transform: isExpanded
                                      ? 'rotate(180deg)'
                                      : 'none',
                                  }}
                                >
                                  expand_more
                                </span>
                              </div>
                            </button>

                            {isExpanded && (
                              <div className="space-y-sm border-t border-outline-variant p-md">
                                {selectableItems.map((item) => (
                                  <label
                                    key={item.id}
                                    className={`flex cursor-pointer items-start gap-md border border-outline-variant p-sm transition-colors ${
                                      isServiceSelected(item.id)
                                        ? 'border-primary bg-primary-container/30'
                                        : 'hover:border-secondary'
                                    }`}
                                  >
                                    <input
                                      type="checkbox"
                                      checked={isServiceSelected(item.id)}
                                      onChange={() => toggleService(item)}
                                      className="mt-1"
                                    />
                                    <div className="flex-1">
                                      <div className="flex items-start justify-between gap-3">
                                        <p className="text-sm font-bold uppercase text-primary">
                                          {item.name}
                                        </p>
                                        <span className="mono-data font-bold text-secondary">
                                          ${Number(item.price || 0).toFixed(2)}
                                        </span>
                                      </div>
                                      <p className="mt-1 text-xs text-outline">
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
                <div className="py-xl text-center text-outline">
                  <p className="font-body-md">
                    Select a main category above to view subcategories and
                    service options.
                  </p>
                </div>
              )}
            </div>
          )}
        </div>

        <div className="shrink-0 flex flex-wrap justify-between gap-3 border-t border-outline-variant p-lg">
          <button
            type="button"
            onClick={onBack || onClose}
            className="border border-outline-variant px-lg py-md text-xs font-label-sm uppercase tracking-widest text-on-surface transition-all hover:bg-surface-container-low active:scale-[0.98]"
          >
            Back To Bike Info
          </button>

          <div className="flex flex-wrap justify-end gap-3">
            <button
              type="button"
              onClick={onClose}
              className="border border-outline-variant px-lg py-md text-xs font-label-sm uppercase tracking-widest text-on-surface transition-all hover:bg-surface-container-low active:scale-[0.98]"
            >
              Cancel
            </button>
            <button
              type="button"
              onClick={handleSubmit}
              disabled={selectedServices.length === 0}
              className="bg-primary px-lg py-md text-xs font-label-sm uppercase tracking-widest text-on-primary transition-all hover:bg-secondary active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
            >
              Confirm Services
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
