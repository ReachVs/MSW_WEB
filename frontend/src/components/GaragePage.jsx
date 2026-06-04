import { useState, useEffect } from 'react'
import AddBikeModal from './AddBikeModal'
import Toast from './Toast'
import ConfirmModal from './ConfirmModal'

export default function GaragePage() {
  // Toggle specs view for each bike
  const [expandedSpecs, setExpandedSpecs] = useState({})

  // Modal states
  const [isAddBikeOpen, setIsAddBikeOpen] = useState(false)
  const [bikes, setBikes] = useState([
    {
      id: 'ducati',
      name: 'DUCATI PANIGALE V4 S',
      vin: 'ZDM123456789',
      serial: 'IT-916-2026-V4S',
      engineCapacity: 1103,
      serviceStatus: 'In Service',
      serviceType: 'ENGINE STRIP',
      lead: 'MARCUS R.',
      milestone: 'Engine Deconstruction & Component Scans',
      image:
        'https://lh3.googleusercontent.com/aida-public/AB6AXuDLsDTOFeQQSdy6K7Qq3LYvWW3UkQNt785g73oUdLyhJS5R52-Relz_jUE7vAvSeNtozjTZXsKLElL1qsOdOP7eAroxHhO2_10Z7UaFy59RiJpWW-wrIw0SH36i9191BkYmA_Iagh0S4Z2ERmyFQS2nI-kxdJ7TgsAnUSBBHmOufLYQZIuzH21EAiJXjBd0yI9Xn1lxzBQLZ4wvX-smIPSZA29-htJdyMQQYI5UmZfSIieU7wFkXPPsIB1AYfAJn_bREh4i4PyfKFtM',
      specs: [
        { name: 'Power Output', value: '215.5 HP @ 13,000 RPM' },
        { name: 'Torque Spec', value: '123.6 Nm @ 9,500 RPM' },
        { name: 'Curb Weight', value: '195.5 KG' },
        { name: 'Compression Ratio', value: '14.0:1' },
        { name: 'Suspension', value: 'Öhlins NPX 25/30 Pressurized Fork' },
      ],
    },
    {
      id: 'bmw',
      name: 'BMW S1000RR M-PACKAGE',
      vin: 'WB129384756',
      serial: 'DE-S1K-2025-M',
      engineCapacity: 999,
      serviceStatus: 'In Diagnosis',
      serviceType: 'ECU CALIBRATION',
      lead: 'MARCUS R.',
      milestone: 'Final ECU Calibration & Exhaust Gas Maps',
      image:
        'https://lh3.googleusercontent.com/aida-public/AB6AXuCOzXLZRAplciXvSYpSnWMedl0MefHQTvsrVbbCLIqXnwU2zqwDnpfGcxfhRQSkwImfWmnLPupMteUKbWDIRB7L79Ty7m9_7ynoziGfnhcSLFXvKSSLP_CzHdupb9vFbvMrx6KoHWAjNxvzg-g49wd6bDEP7uZHsgFugZQmEPwQBG7JxjYXdVUsf6uPP2eFADRKy4i6RitJOAsQZB79CQhLbDPcAzEjyoA_1Hexo_MZFzkhm22bUltiqIX255yzCR7K2KAFKM2AVVAA',
      specs: [
        { name: 'Power Output', value: '207 HP @ 13,500 RPM' },
        { name: 'Torque Spec', value: '113 Nm @ 11,000 RPM' },
        { name: 'Curb Weight', value: '193.5 KG (M Package)' },
        { name: 'Compression Ratio', value: '13.3:1' },
        { name: 'Brakes', value: 'BMW M Calipers, 320mm Double Disc' },
      ],
    },
    {
      id: 'triumph',
      name: 'TRIUMPH STREET TRIPLE 765 RS',
      vin: 'SMT765RS902',
      serial: 'UK-765-2026-RS',
      engineCapacity: 765,
      serviceStatus: 'In Workshop',
      serviceType: 'FLUID FLUSH',
      lead: 'MARCUS R.',
      milestone: 'Fluid Flush & System Optimization',
      progress: 65,
      image:
        'https://lh3.googleusercontent.com/aida-public/AB6AXuD1Y6CYlKoSVOrpPRxL5JbH1Wkj2Z81hpYJSQFxAJ94tPdnkrPo0OCaSHDyPdPhWuB0wMU3b5ayfB_zUBTeiUG1fBF6FEpl6UPtIoApyLwYdbXCYPdsYggdGgkR3310OKntC46LwA7MOjjojscA9jUjlJjJpDsSi-b_31L1ZZNkYsMOFdQ4cnYpr-IbMNX4bSLvbzrHp3h3pFqTyJoU0i-y8X8-4n-kzmV7SzNCN9HgWA_KTSfOmaAJVD427adlMiz2bJZBTxokuyjx',
      specs: [
        { name: 'Power Output', value: '130 HP @ 12,000 RPM' },
        { name: 'Torque Spec', value: '80 Nm @ 9,500 RPM' },
        { name: 'Curb Weight', value: '188 KG' },
        { name: 'Bore & Stroke', value: '78.0 x 53.4 mm' },
        { name: 'Gearbox', value: '6-Speed with Triumph Shift Assist' },
      ],
    },
  ])

  // Toast and confirmation states
  const [toast, setToast] = useState(null)
  const [confirmDelete, setConfirmDelete] = useState(null)

  // Simulated live sensor values
  const [sensors, setSensors] = useState({
    bmwTireTemp: { f: 32.0, r: 34.0 },
    triumphVoltage: 13.8,
    activeSensors: 4,
  })

  // Sensor simulation loop
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

  const toggleSpecs = (id) => {
    setExpandedSpecs((prev) => ({
      ...prev,
      [id]: !prev[id],
    }))
  }

  const handleAddBike = (formData) => {
    const newBike = {
      id: `bike-${Date.now()}`,
      ...formData,
      serial: `SERIAL-${Math.random().toString(36).substr(2, 9).toUpperCase()}`,
      serviceStatus: formData.serviceStatus,
      serviceType: 'READY',
      lead: 'MARCUS R.',
      milestone: 'Awaiting Initial Inspection',
      progress: 0,
      image:
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&h=300&fit=crop',
      specs: [
        { name: 'Engine Capacity', value: `${formData.engineCapacity} CC` },
        { name: 'Status', value: formData.serviceStatus },
        { name: 'Service Type', value: 'General Diagnostics' },
      ],
    }

    setBikes([...bikes, newBike])
    setIsAddBikeOpen(false)
    setToast({
      message: `${formData.name} added to garage!`,
      type: 'success',
    })
  }

  const handleDeleteBike = (bikeId) => {
    const bike = bikes.find((b) => b.id === bikeId)
    setConfirmDelete({
      bikeId,
      bikeName: bike.name,
    })
  }

  const confirmDeleteBike = () => {
    setBikes(bikes.filter((b) => b.id !== confirmDelete.bikeId))
    setToast({
      message: `${confirmDelete.bikeName} removed from garage`,
      type: 'info',
    })
    setConfirmDelete(null)
  }

  return (
    <div className="flex-grow p-lg overflow-y-auto grid-pattern">
      {/* Toast notification */}
      {toast && (
        <Toast
          message={toast.message}
          type={toast.type}
          duration={3000}
          onClose={() => setToast(null)}
        />
      )}

      {/* Delete confirmation dialog */}
      <ConfirmModal
        isOpen={!!confirmDelete}
        title="Remove Motorcycle"
        message={`Are you sure you want to remove ${confirmDelete?.bikeName} from your garage? This action cannot be undone.`}
        confirmText="Remove"
        cancelText="Keep It"
        isDangerous={true}
        onConfirm={confirmDeleteBike}
        onCancel={() => setConfirmDelete(null)}
      />

      {/* Add bike modal */}
      <AddBikeModal
        isOpen={isAddBikeOpen}
        onClose={() => setIsAddBikeOpen(false)}
        onSubmit={handleAddBike}
      />

      <div className="max-w-7xl mx-auto space-y-lg">
        {/* Title Area */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-2">
          <div>
            <h1 className="font-display-xl text-3xl md:text-4xl font-black text-primary uppercase tracking-tighter">
              LIVE WORKSHOP TRACK
            </h1>
            <p className="text-on-surface-variant font-body-md text-sm mt-1 max-w-[36rem]">
              Telemetry links established. Review calibration matrices, engine
              load tolerances, and live service milestones.
            </p>
          </div>
          <div className="flex items-center gap-2 px-3 py-1.5 bg-secondary/10 border border-secondary/20 select-none">
            <span className="w-2.5 h-2.5 rounded-full bg-secondary animate-pulse"></span>
            <span className="font-mono text-[10px] text-secondary font-bold uppercase tracking-widest">
              {sensors.activeSensors} SENSORS ACTIVE
            </span>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex flex-wrap gap-3 mb-6">
          <button
            onClick={() => setIsAddBikeOpen(true)}
            className="bg-secondary text-on-secondary px-lg py-md font-label-sm text-xs uppercase tracking-widest hover:bg-primary transition-all active:scale-[0.98] border border-secondary"
          >
            + Add New Bike
          </button>
        </div>

        {/* Bento Grid of Machines */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-lg animate-fadeIn">
          {bikes.map((bike) => (
            <div
              key={bike.id}
              className={`bg-white border border-outline-variant flex flex-col group transition-all duration-300 shadow-sm hover:shadow-md ${bike.id === 'triumph' ? 'lg:col-span-2' : ''}`}
            >
              <div
                className={`${bike.id === 'triumph' ? 'md:w-1/2' : 'w-full'} aspect-video md:aspect-auto overflow-hidden relative border-b border-outline-variant bg-surface-container-low`}
              >
                <img
                  className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                  src={bike.image}
                  alt={bike.name}
                />
                <div
                  className={`absolute top-4 ${bike.id === 'triumph' ? 'left-4' : 'right-4'} flex flex-col gap-2 items-${bike.id === 'triumph' ? 'start' : 'end'}`}
                >
                  <span
                    className={`font-bold text-[9px] px-3 py-1 uppercase tracking-widest ${
                      bike.serviceStatus === 'In Service'
                        ? 'bg-primary text-on-primary'
                        : bike.serviceStatus === 'In Diagnosis'
                          ? 'bg-secondary text-on-secondary'
                          : 'bg-primary text-on-primary'
                    }`}
                  >
                    {bike.serviceStatus}
                  </span>
                </div>
              </div>

              <div
                className={`${bike.id === 'triumph' ? 'md:w-1/2' : 'w-full'} p-md flex flex-col flex-grow`}
              >
                <div className="mb-6">
                  <h2 className="font-headline-lg text-2xl text-primary leading-tight uppercase font-black mb-1">
                    {bike.name}
                  </h2>
                  <p className="font-mono text-[10px] text-outline uppercase tracking-widest">
                    Serial: {bike.serial}
                  </p>
                </div>
                <hr className="border-outline-variant mb-6" />

                <div className="grid grid-cols-3 gap-6 mb-8">
                  <div className="flex flex-col">
                    <span className="text-outline font-bold text-[9px] uppercase tracking-[0.2em] mb-3">
                      Engine Capacity
                    </span>
                    <div className="flex items-baseline gap-1 mb-2">
                      <span className="font-mono text-2xl font-bold text-primary">
                        {bike.engineCapacity}
                      </span>
                      <span className="font-mono text-outline text-[10px]">
                        CC
                      </span>
                    </div>
                  </div>

                  <div className="flex flex-col">
                    <span className="text-outline font-bold text-[9px] uppercase tracking-[0.2em] mb-3">
                      Active Service
                    </span>
                    <div className="font-mono text-sm md:text-base font-bold text-secondary mb-1 truncate">
                      {bike.serviceType}
                    </div>
                    <div className="font-mono text-[9px] text-outline uppercase">
                      {bike.serviceStatus}
                    </div>
                  </div>

                  <div className="flex flex-col">
                    <span className="text-outline font-bold text-[9px] uppercase tracking-[0.2em] mb-3">
                      Assigned Lead
                    </span>
                    <div className="flex items-baseline gap-1">
                      <span className="font-mono text-sm md:text-base font-bold text-primary">
                        {bike.lead}
                      </span>
                    </div>
                    <span className="font-mono text-[9px] text-outline uppercase mt-1">
                      LEAD TECH
                    </span>
                  </div>
                </div>

                <div className="p-3 border border-secondary/20 bg-secondary/[0.02] mb-6">
                  <div className="flex justify-between items-center mb-1">
                    <span className="font-bold text-[8px] text-secondary uppercase tracking-[0.2em]">
                      Workshop Progress
                    </span>
                    <span className="font-mono text-secondary font-bold text-[10px] uppercase tracking-widest">
                      Active Phase
                    </span>
                  </div>
                  <div className="mt-2 font-mono text-[8px] text-outline uppercase">
                    Milestone: {bike.milestone}
                  </div>
                  {bike.progress !== undefined && (
                    <div className="mt-3 h-1 w-full bg-surface-container-highest">
                      <div
                        className="h-full bg-secondary transition-all duration-1000"
                        style={{ width: `${bike.progress}%` }}
                      ></div>
                    </div>
                  )}
                </div>

                {expandedSpecs[bike.id] && (
                  <div className="mb-6 p-4 bg-surface-container-low border border-outline-variant font-mono text-xs space-y-2 animate-fadeIn">
                    <p className="font-bold uppercase tracking-wider text-[10px] text-primary mb-3">
                      Telemetry Specifications
                    </p>
                    {bike.specs.map((spec) => (
                      <div
                        key={spec.name}
                        className="flex justify-between border-b border-outline-variant pb-1"
                      >
                        <span className="text-on-surface-variant uppercase">
                          {spec.name}
                        </span>
                        <span className="text-primary font-bold">
                          {spec.value}
                        </span>
                      </div>
                    ))}
                  </div>
                )}

                <div className="flex gap-4 mt-auto">
                  <button
                    onClick={() => toggleSpecs(bike.id)}
                    className="py-3.5 border border-primary text-primary font-bold text-[10px] hover:bg-primary hover:text-white transition-all uppercase tracking-widest flex-1 active:scale-[0.98]"
                  >
                    {expandedSpecs[bike.id]
                      ? 'Hide Specifications'
                      : 'Technical Specs'}
                  </button>
                  <button
                    onClick={() => handleDeleteBike(bike.id)}
                    className="py-3.5 border border-error text-error font-bold text-[10px] hover:bg-error hover:text-white transition-all uppercase tracking-widest px-md active:scale-[0.98]"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>

        {bikes.length === 0 && (
          <div className="text-center py-xl">
            <p className="text-on-surface-variant font-body-md text-sm mb-lg">
              No motorcycles in your garage yet.
            </p>
            <button
              onClick={() => setIsAddBikeOpen(true)}
              className="bg-primary text-on-primary px-lg py-md font-label-sm text-xs uppercase tracking-widest hover:bg-secondary transition-all active:scale-95"
            >
              Add Your First Bike
            </button>
          </div>
        )}
      </div>
    </div>
  )
}
