import React, { useState } from 'react'
import Toast from './Toast'

export default function ServiceHistoryPage({ logs }) {
  const [expandedLog, setExpandedLog] = useState(null)
  const [toast, setToast] = useState(null)

  // Filter, Search, Sort, Pagination States
  const [statusFilter, setStatusFilter] = useState('All')
  const [searchQuery, setSearchQuery] = useState('')
  const [sortBy, setSortBy] = useState('newest')
  const [currentPage, setCurrentPage] = useState(1)
  const itemsPerPage = 5

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

  const toggleExpand = (id) => {
    setExpandedLog((prev) => (prev === id ? null : id))
  }

  // Filter logs by status
  const filteredByStatus =
    statusFilter === 'All'
      ? logs
      : logs.filter((log) => log.status === statusFilter)

  // Filter by search query (searches type, bike name, ref ID)
  const filteredBySearch = filteredByStatus.filter((log) => {
    const searchLower = searchQuery.toLowerCase()
    return (
      log.id.toLowerCase().includes(searchLower) ||
      log.unit.toLowerCase().includes(searchLower) ||
      log.serviceType.toLowerCase().includes(searchLower)
    )
  })

  // Sort filtered logs
  const sortedLogs = [...filteredBySearch].sort((a, b) => {
    if (sortBy === 'newest') {
      return filteredBySearch.indexOf(a) - filteredBySearch.indexOf(b)
    } else if (sortBy === 'oldest') {
      return filteredBySearch.indexOf(b) - filteredBySearch.indexOf(a)
    } else if (sortBy === 'price-high') {
      const priceA = parseInt(a.fee.replace(/[^0-9]/g, ''))
      const priceB = parseInt(b.fee.replace(/[^0-9]/g, ''))
      return priceB - priceA
    } else if (sortBy === 'price-low') {
      const priceA = parseInt(a.fee.replace(/[^0-9]/g, ''))
      const priceB = parseInt(b.fee.replace(/[^0-9]/g, ''))
      return priceA - priceB
    }
    return 0
  })

  // Pagination
  const totalPages = Math.ceil(sortedLogs.length / itemsPerPage)
  const startIndex = (currentPage - 1) * itemsPerPage
  const paginatedLogs = sortedLogs.slice(
    startIndex,
    startIndex + itemsPerPage
  )

  // Export function
  const handleExport = () => {
    const exportData = sortedLogs.map((log) => ({
      'Ref ID': log.id,
      'Date': log.date,
      'Unit': log.unit,
      'VIN': log.vin,
      'Service Type': log.serviceType,
      'Fee': log.fee,
      'Status': log.status,
    }))

    const dataStr = JSON.stringify(exportData, null, 2)
    const dataBlob = new Blob([dataStr], { type: 'application/json' })
    const url = URL.createObjectURL(dataBlob)
    const link = document.createElement('a')
    link.href = url
    link.download = `service-history-${new Date().toISOString().split('T')[0]}.json`
    link.click()
    URL.revokeObjectURL(url)

    setToast({
      message: `Exported ${sortedLogs.length} service records`,
      type: 'success',
    })
  }

  return (
    <div className="flex-grow p-lg">
      {/* Toast */}
      {toast && (
        <Toast
          message={toast.message}
          type={toast.type}
          duration={3000}
          onClose={() => setToast(null)}
        />
      )}

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

        {/* Filter, Search, Sort Toolbar */}
        <div className="mb-lg p-md bg-surface-container border border-outline-variant space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            {/* Status Filter */}
            <div>
              <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-2">
                Status Filter
              </label>
              <select
                value={statusFilter}
                onChange={(e) => {
                  setStatusFilter(e.target.value)
                  setCurrentPage(1)
                }}
                className="w-full border border-outline-variant px-3 py-2 font-body-md text-sm focus:outline-none focus:border-secondary"
              >
                <option value="All">All Services</option>
                <option value="Completed">Completed</option>
                <option value="In Progress">In Progress</option>
              </select>
            </div>

            {/* Sort Option */}
            <div>
              <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-2">
                Sort By
              </label>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="w-full border border-outline-variant px-3 py-2 font-body-md text-sm focus:outline-none focus:border-secondary"
              >
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="price-high">Price (High to Low)</option>
                <option value="price-low">Price (Low to High)</option>
              </select>
            </div>

            {/* Search Box */}
            <div className="md:col-span-2">
              <label className="block font-label-sm text-xs text-primary uppercase tracking-widest mb-2">
                Search
              </label>
              <input
                type="text"
                placeholder="Search by ref ID, bike name, service type..."
                value={searchQuery}
                onChange={(e) => {
                  setSearchQuery(e.target.value)
                  setCurrentPage(1)
                }}
                className="w-full border border-outline-variant px-3 py-2 font-body-md text-sm focus:outline-none focus:border-secondary"
              />
            </div>
          </div>

          {/* Export Button */}
          <div className="flex gap-3 justify-end pt-2 border-t border-outline-variant">
            <button
              onClick={handleExport}
              className="border border-primary text-primary px-lg py-md font-label-sm text-xs uppercase tracking-widest hover:bg-primary hover:text-white transition-all active:scale-[0.98]"
            >
              ↓ Export as JSON
            </button>
          </div>
        </div>

        {/* Results Summary */}
        <div className="mb-4 font-label-sm text-xs text-on-surface-variant uppercase tracking-widest">
          Showing {paginatedLogs.length > 0 ? startIndex + 1 : 0} to{' '}
          {Math.min(startIndex + itemsPerPage, sortedLogs.length)} of{' '}
          {sortedLogs.length} records
        </div>

        {/* Records Table */}
        {sortedLogs.length > 0 ? (
          <>
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
                    {paginatedLogs.map((log) => {
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
                              {log.status === 'In Progress' && (
                                <div className="mt-2 h-1.5 w-16 bg-surface-container-highest">
                                  <div className="h-full bg-secondary w-[45%] animate-pulse"></div>
                                </div>
                              )}
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

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="mt-lg flex items-center justify-between">
                <div className="font-label-sm text-xs text-on-surface-variant uppercase tracking-widest">
                  Page {currentPage} of {totalPages}
                </div>
                <div className="flex gap-2">
                  <button
                    onClick={() =>
                      setCurrentPage(Math.max(1, currentPage - 1))
                    }
                    disabled={currentPage === 1}
                    className="border border-outline-variant text-on-surface px-md py-2 font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container-low disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                  >
                    ← Previous
                  </button>
                  <button
                    onClick={() =>
                      setCurrentPage(Math.min(totalPages, currentPage + 1))
                    }
                    disabled={currentPage === totalPages}
                    className="border border-outline-variant text-on-surface px-md py-2 font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container-low disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                  >
                    Next →
                  </button>
                </div>
              </div>
            )}
          </>
        ) : (
          <div className="text-center py-xl border border-outline-variant bg-surface-container-lowest">
            <p className="text-on-surface-variant font-body-md text-sm">
              No service records found matching your filters.
            </p>
          </div>
        )}
      </div>
    </div>
  )
}
