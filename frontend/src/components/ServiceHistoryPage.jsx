import React, { useState } from 'react'

export default function ServiceHistoryPage({ logs }) {
  const [expandedLog, setExpandedLog] = useState(null)

  const toggleExpand = (id) => {
    setExpandedLog((prev) => (prev === id ? null : id))
  }

  // Detailed info database to show when expanded
  const detailsDatabase = {
    'MA-992-04': {
      technician: 'Marcus R. (Lead Technician)',
      checklist: [
        'Desmodromic valve clearance inspection & adjustment (Optimal tolerances met)',
        'Spark plugs replacement (OEM NGK Laser Iridium)',
        'Air filter cartridge swap (Sprint Filter P08)',
        'Timing belt replacement & tensioning',
        'Engine coolant flush & replacement (Motul Motocool)',
      ],
      diagnostics:
        'Fault logs scanned: Clean. Compression test: Cylinder 1 (14.2 bar), Cylinder 2 (14.1 bar), Cylinder 3 (14.2 bar), Cylinder 4 (14.0 bar). Peak tolerances perfectly matched to Ducati factory requirements.',
      partsReplaced:
        'Valve shims, Spark plugs, Air filter, Timing belts, Oil filter',
    },
    'MA-881-12': {
      technician: 'Marcus R. (Lead Technician)',
      checklist: [
        'ECU diagnostic mapping & firmware updates',
        'Dynamic map calibration for Akrapovic titanium full system',
        'Ignition timing optimization (98 RON fuel calibration)',
        'Quickshifter & Auto-blipper trigger times tuning',
        'Ride-by-wire throttle response linearization',
      ],
      diagnostics:
        'Initial: Lean AFR detected in mid-range. Final: Calibrated to target AFR of 13.2:1 across peak torque band. Dyno certified +7.4 WHP gains verified.',
      partsReplaced: 'None (Software recalibration & Dyno diagnostic cycles)',
    },
    'MA-765-90': {
      technician: 'Sarah L. (Fluid Systems Expert)',
      checklist: [
        'Brake fluid flush & replacement (Brembo LCF 600 Plus)',
        'Clutch slave cylinder bleed & fluid flush',
        'Radiator fluid flush & anti-cavitation agent add',
        'Front fork fluid check & seal integrity scan',
      ],
      diagnostics:
        'Brake fluid water content measured at 2.4% prior to service (Warning threshold). Post-flush water content: 0.0%. Brake pressure response restored.',
      partsReplaced: 'Brake fluid, sealing washers, copper crush gaskets',
    },
  }

  return (
    <div className="flex-grow p-lg">
      <div className="max-w-7xl mx-auto">
        {/* Page Header */}
        <div className="mb-xl">
          <h1 className="font-headline-lg text-3xl md:text-4xl text-primary font-black mb-sm tracking-tighter uppercase">
            SERVICE ARCHIVE
          </h1>
          <p className="text-on-surface-variant max-w-2xl font-body-md text-sm">
            Precision maintenance logs for your performance machinery. Review
            historical technical data, part replacements, and technician notes
            curated by our mechanical engineers.
          </p>
        </div>

        {/* Records Table */}
        <div className="border border-primary bg-white shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-primary text-on-primary font-mono text-xs select-none">
                  <th className="py-md px-md font-label-sm tracking-widest uppercase font-bold">
                    REF ID
                  </th>
                  <th className="py-md px-md font-label-sm tracking-widest uppercase font-bold">
                    DATE
                  </th>
                  <th className="py-md px-md font-label-sm tracking-widest uppercase font-bold">
                    UNIT
                  </th>
                  <th className="py-md px-md font-label-sm tracking-widest uppercase font-bold">
                    SERVICE TYPE
                  </th>
                  <th className="py-md px-md font-label-sm tracking-widest uppercase font-bold">
                    FEE
                  </th>
                  <th className="py-md px-md font-label-sm tracking-widest uppercase font-bold">
                    STATUS
                  </th>
                  <th className="py-md px-md font-label-sm tracking-widest uppercase font-bold text-right">
                    ACTION
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-outline-variant font-body-md text-sm">
                {logs.map((log) => {
                  const isExpanded = expandedLog === log.id
                  const details = detailsDatabase[log.id] || {
                    technician: 'Marcus R.',
                    checklist: [
                      'General diagnostic scans completed',
                      'Standard multipoint safety inspection',
                    ],
                    diagnostics:
                      log.notes ||
                      'Routine check. Performance logs registered optimal.',
                    partsReplaced: 'Standard consumables',
                  }

                  return (
                    <React.Fragment key={log.id}>
                      <tr
                        className={`hover:bg-surface-container-low transition-colors group ${isExpanded ? 'bg-surface-container-low' : ''}`}
                      >
                        <td className="py-md px-md mono-data text-label-sm text-secondary font-bold select-all">
                          #{log.id}
                        </td>
                        <td className="py-md px-md text-on-surface-variant font-mono text-xs">
                          {log.date}
                        </td>
                        <td className="py-md px-md">
                          <div className="font-bold text-primary">
                            {log.unit}
                          </div>
                          <div className="text-[10px] text-on-surface-variant opacity-60 uppercase mono-data select-all">
                            VIN: {log.vin}
                          </div>
                        </td>
                        <td className="py-md px-md">
                          <span className="inline-block border border-outline-variant px-sm py-xs font-label-sm text-[10px] uppercase font-bold">
                            {log.serviceType}
                          </span>
                        </td>
                        <td className="py-md px-md mono-data font-bold text-primary">
                          {log.fee}
                        </td>
                        <td className="py-md px-md">
                          <div className="flex items-center space-x-1.5">
                            <span
                              className={`w-2 h-2 rounded-none ${log.status === 'Completed' ? 'bg-green-600' : 'bg-secondary animate-pulse'}`}
                            ></span>
                            <span className="font-label-sm text-[10px] uppercase tracking-wider text-on-surface">
                              {log.status}
                            </span>
                          </div>
                        </td>
                        <td className="py-md px-md text-right">
                          <button
                            onClick={() => toggleExpand(log.id)}
                            className="font-label-sm text-xs text-primary hover:text-secondary transition-colors underline underline-offset-4 decoration-outline-variant group-hover:decoration-secondary uppercase font-bold"
                          >
                            {isExpanded ? 'Collapse Data' : 'Expand Data'}
                          </button>
                        </td>
                      </tr>

                      {/* Expanded Section */}
                      {isExpanded && (
                        <tr className="bg-surface-container-lowest animate-fadeIn">
                          <td
                            colSpan={7}
                            className="p-0 border-t border-outline-variant"
                          >
                            <div className="p-lg grid grid-cols-1 md:grid-cols-12 gap-gutter font-mono text-xs">
                              {/* Left details panel */}
                              <div className="md:col-span-7 space-y-md border-b md:border-b-0 md:border-r border-outline-variant pb-md md:pb-0 md:pr-lg">
                                <div>
                                  <span className="font-bold text-primary uppercase text-[10px] tracking-wider block mb-2">
                                    ENGINEERING CHECKLIST & STEPS
                                  </span>
                                  <ul className="space-y-1.5 text-on-surface-variant">
                                    {details.checklist.map((step, idx) => (
                                      <li
                                        key={idx}
                                        className="flex items-start gap-2"
                                      >
                                        <span className="text-secondary select-none font-bold">
                                          ✓
                                        </span>
                                        <span>{step}</span>
                                      </li>
                                    ))}
                                  </ul>
                                </div>
                                <hr className="border-outline-variant" />
                                <div>
                                  <span className="font-bold text-primary uppercase text-[10px] tracking-wider block mb-1">
                                    DIAGNOSTICS & TELEMETRY NOTES
                                  </span>
                                  <p className="text-on-surface-variant leading-relaxed">
                                    {details.diagnostics}
                                  </p>
                                </div>
                              </div>

                              {/* Right details panel */}
                              <div className="md:col-span-5 space-y-md md:pl-md">
                                <div>
                                  <span className="font-bold text-primary uppercase text-[10px] tracking-wider block mb-1">
                                    ASSIGNED SPECIALIST
                                  </span>
                                  <div className="text-on-surface-variant">
                                    {details.technician}
                                  </div>
                                </div>
                                <hr className="border-outline-variant" />
                                <div>
                                  <span className="font-bold text-primary uppercase text-[10px] tracking-wider block mb-1">
                                    REPLACED COMPONENTS
                                  </span>
                                  <div className="text-secondary font-bold uppercase tracking-wider text-[10px]">
                                    {details.partsReplaced}
                                  </div>
                                </div>
                                <hr className="border-outline-variant" />
                                <div className="p-3 bg-surface-container border border-outline-variant">
                                  <div className="flex justify-between items-center text-[10px] font-bold text-primary">
                                    <span>CERTIFICATION STATUS</span>
                                    <span className="text-green-600">
                                      VERIFIED
                                    </span>
                                  </div>
                                  <p className="text-[10px] text-on-surface-variant mt-1.5 leading-snug">
                                    This machine meets all architectural
                                    tolerances and engine health diagnostic
                                    limits set by MAD APE MOTORWORKS.
                                  </p>
                                </div>
                              </div>
                            </div>
                          </td>
                        </tr>
                      )}
                    </React.Fragment>
                  )
                })}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  )
}
