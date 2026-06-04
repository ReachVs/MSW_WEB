import { useState, useEffect } from 'react'

export default function GaragePage() {
  // Toggle specs view for each bike
  const [expandedSpecs, setExpandedSpecs] = useState({})

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

  // Default specifications for display when expanded
  const technicalSpecsDatabase = {
    ducati: [
      { name: 'Power Output', value: '215.5 HP @ 13,000 RPM' },
      { name: 'Torque Spec', value: '123.6 Nm @ 9,500 RPM' },
      { name: 'Curb Weight', value: '195.5 KG' },
      { name: 'Compression Ratio', value: '14.0:1' },
      { name: 'Suspension', value: 'Öhlins NPX 25/30 Pressurized Fork' },
    ],
    bmw: [
      { name: 'Power Output', value: '207 HP @ 13,500 RPM' },
      { name: 'Torque Spec', value: '113 Nm @ 11,000 RPM' },
      { name: 'Curb Weight', value: '193.5 KG (M Package)' },
      { name: 'Compression Ratio', value: '13.3:1' },
      { name: 'Brakes', value: 'BMW M Calipers, 320mm Double Disc' },
    ],
    triumph: [
      { name: 'Power Output', value: '130 HP @ 12,000 RPM' },
      { name: 'Torque Spec', value: '80 Nm @ 9,500 RPM' },
      { name: 'Curb Weight', value: '188 KG' },
      { name: 'Bore & Stroke', value: '78.0 x 53.4 mm' },
      { name: 'Gearbox', value: '6-Speed with Triumph Shift Assist' },
    ],
  }

  return (
    <div className="flex-grow p-lg overflow-y-auto grid-pattern">
      <div className="max-w-7xl mx-auto space-y-lg">
        {/* Title Area */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-2">
          <div>
            <h1 className="font-display-xl text-3xl md:text-4xl font-black text-primary uppercase tracking-tighter">
              LIVE WORKSHOP TRACK
            </h1>
            <p className="text-on-surface-variant font-body-md text-sm mt-1 max-w-xl">
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

        {/* Bento Grid of Machines */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-lg">
          {/* Machine Card 01: Ducati */}
          <div className="bg-white border border-outline-variant flex flex-col group transition-all duration-300 shadow-sm hover:shadow-md">
            <div className="aspect-video w-full overflow-hidden relative border-b border-outline-variant bg-surface-container-low">
              <img
                className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDLsDTOFeQQSdy6K7Qq3LYvWW3UkQNt785g73oUdLyhJS5R52-Relz_jUE7vAvSeNtozjTZXsKLElL1qsOdOP7eAroxHhO2_10Z7UaFy59RiJpWW-wrIw0SH36i9191BkYmA_Iagh0S4Z2ERmyFQS2nI-kxdJ7TgsAnUSBBHmOufLYQZIuzH21EAiJXjBd0yI9Xn1lxzBQLZ4wvX-smIPSZA29-htJdyMQQYI5UmZfSIieU7wFkXPPsIB1AYfAJn_bREh4i4PyfKFtM"
                alt="Ducati Panigale"
              />
              <div className="absolute top-4 right-4 flex flex-col gap-2 items-end">
                <span className="bg-primary text-on-primary font-bold text-[9px] px-3 py-1 uppercase tracking-widest">
                  IN SERVICE
                </span>
              </div>
            </div>

            <div className="p-md flex flex-col flex-grow">
              <div className="mb-6">
                <h2 className="font-headline-lg text-2xl text-primary leading-tight uppercase font-black mb-1">
                  DUCATI PANIGALE V4 S
                </h2>
                <p className="font-mono text-[10px] text-outline uppercase tracking-widest">
                  Serial: IT-916-2026-V4S
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
                      1103
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
                    ENGINE STRIP
                  </div>
                  <div className="font-mono text-[9px] text-outline uppercase">
                    MIL: PRECISION CLEAN
                  </div>
                </div>

                <div className="flex flex-col">
                  <span className="text-outline font-bold text-[9px] uppercase tracking-[0.2em] mb-3">
                    Assigned Lead
                  </span>
                  <div className="flex items-baseline gap-1">
                    <span className="font-mono text-sm md:text-base font-bold text-primary">
                      MARCUS R.
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
                  Milestone: Engine Deconstruction & Component Scans
                </div>
              </div>

              {expandedSpecs['ducati'] && (
                <div className="mb-6 p-4 bg-surface-container-low border border-outline-variant font-mono text-xs space-y-2 animate-fadeIn">
                  <p className="font-bold uppercase tracking-wider text-[10px] text-primary mb-3">
                    Telemetry Specifications
                  </p>
                  {technicalSpecsDatabase.ducati.map((spec) => (
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
                  onClick={() => toggleSpecs('ducati')}
                  className="py-3.5 border border-primary text-primary font-bold text-[10px] hover:bg-primary hover:text-white transition-all uppercase tracking-widest w-full active:scale-[0.98]"
                >
                  {expandedSpecs['ducati']
                    ? 'Hide Specifications'
                    : 'Technical Specs'}
                </button>
              </div>
            </div>
          </div>

          {/* Machine Card 02: BMW */}
          <div className="bg-white border border-outline-variant flex flex-col group transition-all duration-300 shadow-sm hover:shadow-md">
            <div className="aspect-video w-full overflow-hidden relative border-b border-outline-variant bg-surface-container-low">
              <img
                className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCOzXLZRAplciXvSYpSnWMedl0MefHQTvsrVbbCLIqXnwU2zqwDnpfGcxfhRQSkwImfWmnLPupMteUKbWDIRB7L79Ty7m9_7ynoziGfnhcSLFXvKSSLP_CzHdupb9vFbvMrx6KoHWAjNxvzg-g49wd6bDEP7uZHsgFugZQmEPwQBG7JxjYXdVUsf6uPP2eFADRKy4i6RitJOAsQZB79CQhLbDPcAzEjyoA_1Hexo_MZFzkhm22bUltiqIX255yzCR7K2KAFKM2AVVAA"
                alt="BMW S1000RR"
              />
              <div className="absolute top-4 right-4 flex flex-col gap-2 items-end">
                <span className="bg-secondary text-on-secondary font-bold text-[9px] px-3 py-1 uppercase tracking-widest">
                  IN DIAGNOSIS
                </span>
              </div>
            </div>

            <div className="p-md flex flex-col flex-grow">
              <div className="mb-6">
                <h2 className="font-headline-lg text-2xl text-primary leading-tight uppercase font-black mb-1">
                  BMW S1000RR M-PACKAGE
                </h2>
                <p className="font-mono text-[10px] text-outline uppercase tracking-widest">
                  Serial: DE-S1K-2025-M
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
                      999
                    </span>
                    <span className="font-mono text-outline text-[10px]">
                      CC
                    </span>
                  </div>
                </div>

                <div className="flex flex-col">
                  <span className="text-outline font-bold text-[9px] uppercase tracking-[0.2em] mb-3">
                    Tire Temp
                  </span>
                  <div className="font-mono text-sm md:text-base font-bold text-secondary mb-1">
                    OPTIMAL
                  </div>
                  <div className="font-mono text-[9px] text-outline uppercase">
                    F: {sensors.bmwTireTemp.f}°C | R: {sensors.bmwTireTemp.r}°C
                  </div>
                </div>

                <div className="flex flex-col">
                  <span className="text-outline font-bold text-[9px] uppercase tracking-[0.2em] mb-3">
                    Assigned Lead
                  </span>
                  <div className="flex items-baseline gap-1">
                    <span className="font-mono text-sm md:text-base font-bold text-primary">
                      MARCUS R.
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
                <div className="font-mono text-[8px] text-outline uppercase mt-2">
                  Milestone: Final ECU Calibration &amp; Exhaust Gas Maps
                </div>
              </div>

              {expandedSpecs['bmw'] && (
                <div className="mb-6 p-4 bg-surface-container-low border border-outline-variant font-mono text-xs space-y-2 animate-fadeIn">
                  <p className="font-bold uppercase tracking-wider text-[10px] text-primary mb-3">
                    Telemetry Specifications
                  </p>
                  {technicalSpecsDatabase.bmw.map((spec) => (
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

              <button
                onClick={() => toggleSpecs('bmw')}
                className="w-full py-3.5 border border-primary text-primary font-bold text-[10px] hover:bg-primary hover:text-white transition-all uppercase tracking-widest active:scale-[0.98]"
              >
                {expandedSpecs['bmw']
                  ? 'Hide Specifications'
                  : 'Technical Specs'}
              </button>
            </div>
          </div>

          {/* Machine Card 03: Triumph (Full-width) */}
          <div className="lg:col-span-2 bg-white border border-outline-variant flex flex-col md:flex-row group transition-all duration-300 shadow-sm hover:shadow-md">
            <div className="md:w-1/2 aspect-video md:aspect-auto overflow-hidden relative bg-surface-container-low border-b md:border-b-0 md:border-r border-outline-variant">
              <img
                className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuD1Y6CYlKoSVOrpPRxL5JbH1Wkj2Z81hpYJSQFxAJ94tPdnkrPo0OCaSHDyPdPhWuB0wMU3b5ayfB_zUBTeiUG1fBF6FEpl6UPtIoApyLwYdbXCYPdsYggdGgkR3310OKntC46LwA7MOjjojscA9jUjlJjJpDsSi-b_31L1ZZNkYsMOFdQ4cnYpr-IbMNX4bSLvbzrHp3h3pFqTyJoU0i-y8X8-4n-kzmV7SzNCN9HgWA_KTSfOmaAJVD427adlMiz2bJZBTxokuyjx"
                alt="Triumph Street Triple"
              />
              <div className="absolute top-4 left-4">
                <span className="bg-primary text-on-primary font-bold text-[9px] px-3 py-1 uppercase tracking-widest shadow-lg">
                  IN WORKSHOP
                </span>
              </div>
            </div>

            <div className="md:w-1/2 p-md md:p-lg flex flex-col justify-between">
              <div>
                <div className="flex justify-between items-start mb-6">
                  <div>
                    <h2 className="font-headline-lg text-2xl text-primary leading-tight uppercase font-black mb-1">
                      TRIUMPH STREET TRIPLE 765 RS
                    </h2>
                    <p className="font-mono text-[10px] text-outline uppercase tracking-widest">
                      Serial: UK-765-2026-RS
                    </p>
                  </div>
                  <div className="text-right">
                    <div className="text-outline font-bold text-[8px] uppercase tracking-widest mb-1">
                      Job Status
                    </div>
                    <div className="font-mono text-primary font-bold text-xs uppercase tracking-tight">
                      Fluid Flush
                    </div>
                  </div>
                </div>

                <hr className="border-outline-variant mb-6" />

                <div className="grid grid-cols-2 gap-4 mb-6">
                  <div className="p-4 bg-surface-container-low border border-outline-variant">
                    <span className="text-outline font-bold text-[9px] uppercase tracking-widest mb-3 block">
                      Battery Voltage
                    </span>
                    <div className="flex items-baseline gap-1">
                      <span className="font-mono text-xl font-bold text-primary">
                        {sensors.triumphVoltage}
                      </span>
                      <span className="font-mono text-outline text-[10px]">
                        V
                      </span>
                    </div>
                    <div className="font-mono text-[9px] text-secondary font-bold uppercase tracking-widest mt-2">
                      Healthy
                    </div>
                  </div>
                  <div className="p-4 bg-surface-container-low border border-outline-variant">
                    <span className="text-outline font-bold text-[9px] uppercase tracking-widest mb-3 block">
                      Oil Life
                    </span>
                    <div className="flex items-baseline gap-1">
                      <span className="font-mono text-xl font-bold text-primary">
                        94
                      </span>
                      <span className="font-mono text-outline text-[10px]">
                        %
                      </span>
                    </div>
                    <div className="font-mono text-[9px] text-outline uppercase mt-2">
                      Synth-4 Synthetic
                    </div>
                  </div>
                </div>

                <div className="p-4 border border-secondary/20 bg-secondary/[0.02] mb-6">
                  <div className="flex justify-between items-center mb-3">
                    <span className="font-bold text-[9px] text-secondary uppercase tracking-[0.2em]">
                      Workshop Progress
                    </span>
                    <span className="font-mono text-secondary font-bold text-xs">
                      65%
                    </span>
                  </div>
                  <div className="h-1 w-full bg-surface-container-highest">
                    <div className="h-full bg-secondary w-[65%] transition-all duration-1000"></div>
                  </div>
                </div>

                {expandedSpecs['triumph'] && (
                  <div className="mb-6 p-4 bg-surface-container-low border border-outline-variant font-mono text-xs space-y-2 animate-fadeIn">
                    <p className="font-bold uppercase tracking-wider text-[10px] text-primary mb-3">
                      Telemetry Specifications
                    </p>
                    {technicalSpecsDatabase.triumph.map((spec) => (
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
              </div>

              <div className="flex gap-4 mt-auto">
                <button
                  onClick={() => toggleSpecs('triumph')}
                  className="py-3.5 border border-primary text-primary font-bold text-[10px] hover:bg-primary hover:text-white transition-all uppercase tracking-widest w-full active:scale-[0.98]"
                >
                  {expandedSpecs['triumph']
                    ? 'Hide Specifications'
                    : 'Technical Specs'}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
