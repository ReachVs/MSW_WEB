import { useState } from 'react'
import Navbar from './components/Navbar'
import Sidebar from './components/Sidebar'
import BookingModal from './components/BookingModal'
import LoginPage from './components/LoginPage'
import LandingPage from './components/LandingPage'
import GaragePage from './components/GaragePage'
import ServiceHistoryPage from './components/ServiceHistoryPage'
import ProfilePage from './components/ProfilePage'
import './index.css'

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [currentView, setCurrentView] = useState('landing')
  const [isBookingOpen, setIsBookingOpen] = useState(false)

  // Custom pre-selected service for the booking modal
  const [preSelectedService, setPreSelectedService] = useState('Desmo Service')

  // Shared state: user bikes
  const [bikes] = useState([
    { id: 'ducati', name: 'DUCATI PANIGALE V4 S', vin: 'ZDM123456789' },
    { id: 'bmw', name: 'BMW S1000RR M-PACKAGE', vin: 'WB129384756' },
    { id: 'triumph', name: 'TRIUMPH STREET TRIPLE 765 RS', vin: 'SMT765RS902' },
  ])

  // Shared state: service history logs
  const [logs, setLogs] = useState([
    {
      id: 'MA-992-04',
      date: '24 OCT 2023',
      unit: 'Ducati Panigale V4 S',
      vin: 'ZDM123456789',
      serviceType: 'Desmo Service',
      fee: '$1,240.00',
      status: 'Completed',
      notes: 'Desmo service completed. All tolerances nominal.',
    },
    {
      id: 'MA-881-12',
      date: '12 SEP 2023',
      unit: 'BMW S1000RR M',
      vin: 'WB129384756',
      serviceType: 'ECU Tuning',
      fee: '$450.00',
      status: 'Completed',
      notes: 'Custom ECU map calibration. Dyno verification complete.',
    },
    {
      id: 'MA-765-90',
      date: '15 AUG 2023',
      unit: 'Triumph Street Triple 765 RS',
      vin: 'SMT765RS902',
      serviceType: 'Fluid Flush',
      fee: '$220.00',
      status: 'Completed',
      notes: 'Brake fluid flushed, high temperature Brembo oil loaded.',
    },
  ])

  const handleLogin = () => {
    setIsAuthenticated(true)
    setCurrentView('garage')
  }

  const handleLogout = () => {
    setIsAuthenticated(false)
    setCurrentView('landing')
  }

  const handleBookServiceSubmit = (bookingData) => {
    // Generate a random Reference ID
    const refNum = Math.floor(100 + Math.random() * 900)
    const refId = `MA-${refNum}-01`

    // Find the VIN if it matches a known bike
    const matchingBike = bikes.find(
      (b) => b.name.toUpperCase() === bookingData.bikeName.toUpperCase(),
    )
    const vin = matchingBike ? matchingBike.vin : 'CUSTOM-MEMBER-BIKE'

    const newLog = {
      id: refId,
      date: bookingData.date,
      unit: bookingData.bikeName,
      vin,
      serviceType: bookingData.serviceType,
      fee: '$' + Math.floor(150 + Math.random() * 800) + '.00',
      status: 'In Progress',
      notes:
        bookingData.notes || 'Order placed. Technician inspection scheduled.',
    }

    setLogs([newLog, ...logs])

    // Redirect to Service History to see the newly added log
    setCurrentView('history')
  }

  const triggerBooking = (defaultService = 'Desmo Service') => {
    setPreSelectedService(defaultService)
    setIsBookingOpen(true)
  }

  // Render view depending on navigation routing
  const renderViewContent = () => {
    if (!isAuthenticated) {
      return <LoginPage onLoginSuccess={handleLogin} />
    }

    switch (currentView) {
      case 'landing':
        return (
          <LandingPage
            onBookService={() => triggerBooking()}
            onExplore={() => {
              setCurrentView('garage')
            }}
          />
        )
      case 'garage':
        return <GaragePage />
      case 'history':
        return <ServiceHistoryPage logs={logs} />
      case 'catalog':
        return (
          <CatalogPage onBook={(serviceName) => triggerBooking(serviceName)} />
        )
      case 'profile':
        return <ProfilePage onLogout={handleLogout} />
      case 'about':
      case 'contact':
        return <PlaceholderPage viewName={currentView} />
      default:
        return (
          <LandingPage
            onBookService={() => triggerBooking()}
            onExplore={() => setCurrentView('garage')}
          />
        )
    }
  }

  return (
    <div className="min-h-screen flex flex-col bg-background text-on-background selection:bg-secondary selection:text-on-secondary">
      {/* Show content based on authentication */}
      {!isAuthenticated ? (
        // Login Page - Full Screen
        renderViewContent()
      ) : (
        <>
          {/* Decorative thin left aside for landing page / guest view */}
          {!isAuthenticated && (
            <aside className="fixed top-0 left-0 h-full w-16 bg-primary border-r border-secondary/20 z-30"></aside>
          )}

          {/* Main sidebar for authenticated screens */}
          {isAuthenticated && (
            <Sidebar
              currentView={currentView}
              onNavigate={setCurrentView}
              onBookService={() => triggerBooking()}
            />
          )}

          {/* Main app block */}
          <div className={`flex flex-col flex-1 ${isAuthenticated ? 'ml-0 md:ml-64' : 'ml-16'}`}>
            {/* Top Navbar */}
            <Navbar
              isAuthenticated={isAuthenticated}
              currentView={currentView}
              onLogin={handleLogin}
              onLogout={handleLogout}
              onNavigate={setCurrentView}
            />

            {/* Dynamic page content */}
            <main className="flex-grow flex flex-col">{renderViewContent()}</main>
          </div>

          {/* Modal dialog */}
          <BookingModal
            isOpen={isBookingOpen}
            onClose={() => setIsBookingOpen(false)}
            onSubmit={handleBookServiceSubmit}
            bikes={bikes}
            defaultService={preSelectedService}
          />
        </>
      )}
    </div>
  )
}

// Inline Sub-Pages for completeness & modularity

function CatalogPage({ onBook }) {
  const services = [
    {
      name: 'Desmo Service',
      desc: 'Clinical valve adjustments, belt tension checks, fluid overrides.',
      price: '$1,240',
      duration: '3-5 Days',
    },
    {
      name: 'ECU Tuning',
      desc: 'Custom diagnostic map integration, dyno tuning calibration, ignition profiles.',
      price: '$450',
      duration: '24 Hours',
    },
    {
      name: 'Fluid Flush',
      desc: 'Brembo high temperature racing brake fluid swap, coolants and oils.',
      price: '$220',
      duration: '2 Hours',
    },
    {
      name: 'Engine Stripdown',
      desc: 'Total mechanical deconstruction, micro-tolerance scans, cleanroom washes.',
      price: 'Custom Quote',
      duration: '7-14 Days',
    },
    {
      name: 'General Diagnostics',
      desc: 'Aerospace sensor attachment, compression checks, error log sweeps.',
      price: '$150',
      duration: '4 Hours',
    },
    {
      name: 'Custom Fabrication',
      desc: 'Five-axis CNC custom metalwork, exhaust headers, bracket architectures.',
      price: 'Custom Quote',
      duration: 'Variable',
    },
  ]

  return (
    <div className="p-lg max-w-7xl mx-auto w-full">
      <div className="mb-xl">
        <h1 className="font-headline-lg text-3xl md:text-4xl text-primary font-black mb-sm tracking-tighter uppercase">
          CATALOG SERVICE
        </h1>
        <p className="text-on-surface-variant max-w-2xl font-body-md text-sm">
          Browse our certified mechanical procedures. Every service tier
          utilizes state-of-the-art diagnostic machinery and premium tolerances.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
        {services.map((srv) => (
          <div
            key={srv.name}
            className="bg-white border border-outline-variant p-md flex flex-col justify-between hover:border-secondary transition-colors duration-300"
          >
            <div>
              <div className="flex justify-between items-start mb-4">
                <h3 className="font-headline-md text-lg text-primary font-bold uppercase">
                  {srv.name}
                </h3>
                <span className="font-mono text-xs text-secondary font-bold">
                  {srv.price}
                </span>
              </div>
              <p className="text-on-surface-variant font-body-md text-xs mb-6">
                {srv.desc}
              </p>
            </div>

            <div className="border-t border-outline-variant pt-4 flex justify-between items-center">
              <span className="font-mono text-[9px] text-outline uppercase tracking-wider">
                Est. Time: {srv.duration}
              </span>
              <button
                onClick={() => onBook(srv.name)}
                className="bg-primary text-on-primary hover:bg-secondary px-4 py-2 font-label-sm text-[10px] uppercase tracking-widest transition-all"
              >
                Book Proced.
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

function PlaceholderPage({ viewName }) {
  return (
    <div className="p-lg max-w-4xl mx-auto w-full flex-grow flex flex-col justify-center items-center text-center">
      <div className="bg-primary text-on-primary inline-block px-sm py-xs mb-md font-label-sm text-xs uppercase tracking-[0.2em]">
        Status: Online
      </div>
      <h1 className="font-headline-lg text-3xl md:text-5xl text-primary font-black uppercase tracking-tighter mb-4">
        {viewName.toUpperCase()} DETAILS
      </h1>
      <p className="text-on-surface-variant font-body-lg max-w-md mb-lg">
        This portal section is active. Standard technical information is routed
        directly to our physical workshop coordinators.
      </p>
      <div className="w-16 h-1 bg-secondary"></div>
    </div>
  )
}

export default App
