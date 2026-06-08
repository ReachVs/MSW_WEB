import React, { useState, useEffect } from 'react'

function formatStatusLabel(status) {
  if (status === 'completed') return 'Completed'
  if (status === 'cancelled') return 'Cancelled'
  if (status === 'ready_pickup') return 'Ready Pickup'
  if (status === 'waiting_part') return 'Waiting Part'
  return (status || 'completed').replace(/_/g, ' ')
}

export default function ServiceHistoryPage() {
  const [expandedLog, setExpandedLog] = useState(null)

  const [logs, setLogs] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [deletingId, setDeletingId] = useState(null)
  const [actionMessage, setActionMessage] = useState(null)

  // Filter, Search, Sort, Pagination States
  const [statusFilter, setStatusFilter] = useState('completed')
  const [searchQuery, setSearchQuery] = useState('')
  const [sortBy, setSortBy] = useState('newest')
  const [currentPage, setCurrentPage] = useState(1)
  const itemsPerPage = 5

  useEffect(() => {
    const fetchCompletedBookings = async () => {
      const token = localStorage.getItem('authToken')
      if (!token) {
        setError('Authentication token not found. Please log in.')
        setLoading(false)
        return
      }

      try {
        const response = await fetch('http://localhost:8080/api/bookings/history', {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
        })

        if (!response.ok) {
          if (response.status === 401) {
            setError('Unauthorized. Please log in again.')
          } else {
            setError(`Failed to fetch service history: ${response.statusText}`)
          }
          setLoading(false)
          return
        }

        const data = await response.json()

        const mappedLogs = data.data.map((booking) => ({
          id: booking.id,
          date: new Date(booking.ends_at || booking.updated_at).toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
          }).toUpperCase(),
          sortDate: booking.updated_at || booking.ends_at,
          unit: booking.bike_name ? `${booking.bike_name} ${booking.model}` : (booking.notes || booking.service_name),
          plateNumber: booking.plate_number || 'N/A',
          serviceType: booking.service_name,
          fee: '$' + Number(booking.total_amount || 0).toFixed(2),
          status: booking.status,
          statusLabel: formatStatusLabel(booking.status),
          notes: booking.notes,
          mechanic: booking.mechanic ? booking.mechanic.name : 'Unassigned',
        }))
        setLogs(mappedLogs)
      } catch (err) {
        setError(`Network error: ${err.message}`)
      } finally {
        setLoading(false)
      }
    }

    fetchCompletedBookings()
  }, [])

  // Detailed info database (kept for now, but ideally would come from backend)
  const detailsDatabase = {
    // These are placeholders, actual data should come from backend if available
    // For now, we'll use log.notes and log.mechanic for diagnostics and technician
    'default': {
      checklist: [
        'General diagnostic scans completed',
        'Standard multipoint safety inspection',
      ],
      partsReplaced: 'Standard consumables',
    }
  }

  const toggleExpand = (id) => {
    setExpandedLog((prev) => (prev === id ? null : id))
  }

  const handleRemoveLog = async (logId) => {
    const token = localStorage.getItem('authToken')

    if (!token) {
      setError('Authentication token not found. Please log in.')
      return
    }

    const confirmed = window.confirm(
      'Remove this archived service record from your history?'
    )

    if (!confirmed) {
      return
    }

    setDeletingId(logId)
    setActionMessage(null)
    setError(null)

    try {
      const response = await fetch(`http://localhost:8080/api/bookings/${logId}`, {
        method: 'DELETE',
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      })

      const data = await response.json().catch(() => ({}))

      if (!response.ok) {
        throw new Error(
          data.message || 'Failed to remove the selected archive record.'
        )
      }

      setLogs((prevLogs) => prevLogs.filter((log) => log.id !== logId))
      setExpandedLog((prevExpanded) =>
        prevExpanded === logId ? null : prevExpanded
      )
      setActionMessage(data.message || 'Service archive record removed.')
    } catch (err) {
      setError(err.message || 'Failed to remove the selected archive record.')
    } finally {
      setDeletingId(null)
    }
  }

  const filteredByStatus =
    statusFilter === 'all'
      ? logs
      : logs.filter((log) => log.status === statusFilter)

  // Filter by search query (searches type, bike name, ref ID)
  const filteredBySearch = filteredByStatus.filter((log) => {
    const searchLower = searchQuery.toLowerCase()
    return (
      String(log.id).toLowerCase().includes(searchLower) || // Ensure ID is string for search
      log.unit.toLowerCase().includes(searchLower) ||
      log.serviceType.toLowerCase().includes(searchLower)
    )
  })

  // Sort filtered logs
  const sortedLogs = [...filteredBySearch].sort((a, b) => {
    if (sortBy === 'newest') {
      return new Date(b.sortDate) - new Date(a.sortDate)
    } else if (sortBy === 'oldest') {
      return new Date(a.sortDate) - new Date(b.sortDate)
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
  const paginatedLogs = sortedLogs.slice(startIndex, startIndex + itemsPerPage)

  useEffect(() => {
    if (currentPage > 1 && currentPage > Math.max(totalPages, 1)) {
      setCurrentPage(Math.max(totalPages, 1))
    }
  }, [currentPage, totalPages])

  if (loading) {
    return (
      <div className="flex-grow p-lg flex items-center justify-center">
        <p className="text-primary font-body-md">Loading service history...</p>
      </div>
    )
  }

  if (error) {
    return (
      <div className="flex-grow p-lg flex items-center justify-center">
        <p className="text-secondary font-body-md">Error: {error}</p>
      </div>
    )
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
                <option value="completed">Completed Services</option>
                <option value="cancelled">Cancelled Services</option>
                <option value="all">All History</option>
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

        </div>

        {/* Results Summary */}
        <div className="mb-4 font-label-sm text-xs text-on-surface-variant uppercase tracking-widest">
          Showing {paginatedLogs.length > 0 ? startIndex + 1 : 0} to{' '}
          {Math.min(startIndex + itemsPerPage, sortedLogs.length)} of{' '}
          {sortedLogs.length} records
        </div>

        {actionMessage && (
          <div className="mb-4 border border-green-600 bg-green-50 px-4 py-3 font-body-md text-sm text-green-700">
            {actionMessage}
          </div>
        )}

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
                      const details = detailsDatabase['default'] // Use default details
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
                                Plate: {log.plateNumber}
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
                                  className={`w-2 h-2 rounded-none ${log.status === 'completed' ? 'bg-green-600' : 'bg-error'}`}
                                ></span>
                                <span className="font-label-sm text-[10px] uppercase tracking-wider text-on-surface">
                                  {log.statusLabel}
                                </span>
                              </div>
                            </td>
                            <td className="py-md px-md text-right">
                              <div className="flex items-center justify-end gap-3">
                                <button
                                  onClick={() => toggleExpand(log.id)}
                                  className="font-label-sm text-xs text-primary hover:text-secondary transition-colors underline underline-offset-4 decoration-outline-variant group-hover:decoration-secondary uppercase font-bold"
                                >
                                  {isExpanded ? 'Collapse Data' : 'Expand Data'}
                                </button>
                                <button
                                  type="button"
                                  onClick={() => handleRemoveLog(log.id)}
                                  disabled={deletingId === log.id}
                                  className="font-label-sm text-xs text-red-600 hover:text-red-700 transition-colors uppercase font-bold disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                  {deletingId === log.id ? 'Removing...' : 'Remove'}
                                </button>
                              </div>
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
                                        {log.notes || details.diagnostics}
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
                                        {log.mechanic}
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
                    onClick={() => setCurrentPage(Math.max(1, currentPage - 1))}
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
