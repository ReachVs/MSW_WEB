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

    if (!isAuthenticated) {
      setCurrentView('login')
      return
    }

    setIsBookingOpen(true)
  }

  // Render view depending on navigation routing
  const renderViewContent = () => {
    const protectedViews = ['garage', 'history', 'profile']

    if (
      !isAuthenticated &&
      (currentView === 'login' || protectedViews.includes(currentView))
    ) {
      return <LoginPage onLoginSuccess={handleLogin} />
    }

    switch (currentView) {
      case 'landing':
        return (
          <LandingPage
            onBookService={() => triggerBooking()}
            onExplore={() => setCurrentView('catalog')}
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
        return isAuthenticated ? (
          <ProfilePage onLogout={handleLogout} />
        ) : (
          <LoginPage onLoginSuccess={handleLogin} />
        )
      case 'login':
        return isAuthenticated ? (
          <GaragePage />
        ) : (
          <LoginPage onLoginSuccess={handleLogin} />
        )
      case 'about':
      case 'contact':
        return <PlaceholderPage viewName={currentView} />
      default:
        return (
          <LandingPage
            onBookService={() => triggerBooking()}
            onExplore={() => setCurrentView('catalog')}
          />
        )
    }
  }

  return (
    <div className="min-h-screen flex flex-col bg-background text-on-background selection:bg-secondary selection:text-on-secondary">
      {!isAuthenticated && currentView === 'login' ? (
        renderViewContent()
      ) : (
        <>
          {/* Decorative thin left aside for guest public views */}
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

          {/* Top Navbar */}
          <Navbar
            isAuthenticated={isAuthenticated}
            currentView={currentView}
            onLogin={() => setCurrentView('login')}
            onLogout={handleLogout}
            onNavigate={setCurrentView}
          />

          {/* Main app block */}
          <div className={`flex flex-col flex-1 ${isAuthenticated ? 'ml-0 md:ml-64' : 'ml-16'}`}>
            {/* Dynamic page content */}
            <main className="flex-grow flex flex-col">{renderViewContent()}</main>
            {!isAuthenticated && <SiteFooter />}
          </div>

          {isAuthenticated && (
            <BookingModal
              isOpen={isBookingOpen}
              onClose={() => setIsBookingOpen(false)}
              onSubmit={handleBookServiceSubmit}
              bikes={bikes}
              defaultService={preSelectedService}
            />
          )}
        </>
      )}
    </div>
  )
}

// Inline Sub-Pages for completeness & modularity

function CatalogPage({ onBook }) {
  const detailPackages = [
    ['SOP_01.1', 'Basic Wash', '$25', 'arrow_forward'],
    ['SOP_01.2', 'Standard Care', '$45', 'verified'],
    ['SOP_01.3', 'Premium Detailing', '$85', 'bolt'],
  ]

  const maintenanceGroups = [
    {
      icon: 'opacity',
      title: 'Fluid Systems',
      tint: '',
      items: [
        ['Coolant Level & Quality', 'CHECK'],
        ['Brake Fluid Hydroscopy', 'CHECK'],
        ['Clutch System Pressure', 'CHECK'],
      ],
    },
    {
      icon: 'slow_motion_video',
      title: 'Kinetic Systems',
      tint: 'bg-surface-container-low',
      items: [
        ['Pad & Disc Measurement', 'MEASURE'],
        ['Caliper Piston Cleaning', 'SERVICE'],
        ['Cable Tension & Lube', 'ADJUST'],
      ],
    },
    {
      icon: 'architecture',
      title: 'Geometry',
      tint: '',
      items: [
        ['Headstock Bearings', 'CHECK'],
        ['Front Fork Seal Integrity', 'INSPECT'],
        ['Rear Shock Damping', 'TEST'],
      ],
    },
    {
      icon: 'settings_input_component',
      title: 'Drivetrain',
      tint: '',
      items: [
        ['Tire Pressure & Wear', 'PSI/MM'],
        ['Chain/Belt Alignment', 'ALIGN'],
        ['Linkage Lubrication', 'LUBE'],
      ],
    },
    {
      icon: 'electric_bolt',
      title: 'Electronics',
      tint: '',
      items: [
        ['Battery Load Test', 'VOLTS'],
        ['Lighting Output Test', 'LUMEN'],
        ['Harness Integrity Check', 'SIGNAL'],
      ],
    },
  ]

  const parts = [
    {
      code: 'UPGRADE_01',
      name: 'Brembo Z04 Pads',
      price: '$85',
      image:
        'https://lh3.googleusercontent.com/aida-public/AB6AXuCHCZCTSxhw5oE4zX_R-ct2XxybEFRmFsAlpiAZNn5sJ4TaAOxFCqxaDnKhW4DJUk1JZK0v2Qzimgi1Ac0SrRQ6DJ5xk_5C9-E467F2bbQpevMa0i2f1tlvZ5QJEVUDY6xlPQ6vErFyomPU5Ad60wZ2Nf-TS9Am7EXY81JVHlbmQGvgjpRTfUFjLm95bc6NqjNPSRWKk3BN7g8grnp-nHsgiqX6q46GvUlJzgR0a3ZUKS47VaYKXafj5ND3qeNxPHeVy3iEKtpNa_b0',
    },
    {
      code: 'UPGRADE_02',
      name: 'Ohlins TTX Shock',
      price: '$1,200',
      image:
        'https://lh3.googleusercontent.com/aida-public/AB6AXuBb-CqGLsln2uhZrQBboCc4LOsleeW7dWkwbDQyaNlwM2wGfKpYWcI3BWz3pgUId7bKmNsdxcxGNZpC-ByHtv75efpmzxxhIngDd64W_NJQ35Gfo2356sG85KwxMgS_t63X0Jw2vfG_aFiq3eBJeRAujlRJAOCGUTWMRDUG39Ts1v0uh_B6wtN5AjHgB-ySSihehDPfGkwS9dS11EZFLKCSKuuWz9O3SLZj7e0XPyT4lb2CHzc7TsqF8Drq8NXZMfOzIWj414vWpZA6',
    },
    {
      code: 'UPGRADE_03',
      name: 'Akrapovic Full Ti',
      price: '$2,400',
      image:
        'https://lh3.googleusercontent.com/aida-public/AB6AXuApAPRhz0GpO8cn05wUf4uhHaCxUd-th-U7WuVCVA8PdZ3v2NXn3BfaxL5Be4PotoeaUCthal1p1sN8UqiX3sbloXwm719dkssG9E_TQk7n2vTQjT9gK08a7O6qYB3L4muhMCbr323oiRv__r3pYKIuRN9zm2uwUIpkFqEGVFBq1asMI-zQnRqHCkPfEBFeRNFZrqdL8o9ew-6jynCOskdHhP356iQ_gE92oLVZr7N5cab5yGrXZEGQy5PjFeJRS1-ErsK2CvPnHCvZ',
    },
  ]

  return (
    <div className="p-lg w-full technical-grid">
      <div className="max-w-[1400px] mx-auto">
        <div className="mb-xl border-b border-outline-variant pb-md">
          <h1 className="font-display-xl text-4xl md:text-7xl text-primary mb-xs uppercase tracking-tighter">
            TECHNICAL CATALOG
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

        <div className="grid grid-cols-12 gap-gutter">
          <div className="col-span-12 lg:col-span-9 space-y-xl">
            <section>
              <SectionTitle number="01" title="Precision Detailing" />
              <div className="grid grid-cols-1 md:grid-cols-3 gap-gutter">
                {detailPackages.map(([code, name, price, icon], index) => (
                  <button
                    key={code}
                    onClick={() => onBook(name)}
                    className={`group border p-md transition-all flex flex-col justify-between h-48 text-left relative overflow-hidden active:scale-[0.98] ${
                      index === 1
                        ? 'border-primary hover:bg-primary-container hover:text-white'
                        : 'border-outline-variant hover:border-secondary bg-white'
                    }`}
                  >
                    <div className="z-10">
                      <p className="font-label-sm text-xs text-outline mb-1 uppercase">
                        {code}
                      </p>
                      <h3 className="font-headline-md text-xl font-bold uppercase">
                        {name}
                      </h3>
                    </div>
                    <div className="flex justify-between items-end z-10">
                      <span
                        className={`mono-data text-2xl font-bold ${index === 2 ? 'text-secondary' : ''}`}
                      >
                        {price}
                      </span>
                      <span className="material-symbols-outlined text-outline group-hover:text-secondary transition-transform group-hover:translate-x-1">
                        {icon}
                      </span>
                    </div>
                  </button>
                ))}
              </div>
            </section>

            <section>
              <SectionTitle number="02" title="System Maintenance" />
              <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 border border-outline-variant bg-white">
                {maintenanceGroups.map((group) => (
                  <div
                    key={group.title}
                    className={`p-md border-b md:border-r border-outline-variant ${group.tint}`}
                  >
                    <h3 className="font-label-sm text-xs uppercase mb-md text-primary flex items-center gap-2">
                      <span className="material-symbols-outlined text-sm">
                        {group.icon}
                      </span>
                      {group.title}
                    </h3>
                    <ul className="space-y-2">
                      {group.items.map(([item, action]) => (
                        <li key={item}>
                          <input
                            className="hidden service-item-check"
                            id={`srv-${item}`}
                            type="checkbox"
                          />
                          <label
                            className="flex justify-between items-center p-2 text-sm border border-transparent hover:border-outline-variant cursor-pointer transition-all"
                            htmlFor={`srv-${item}`}
                          >
                            <span>{item}</span>
                            <span className="mono-data text-xs text-outline">
                              {action}
                            </span>
                          </label>
                        </li>
                      ))}
                    </ul>
                  </div>
                ))}
                <div className="p-md bg-primary-container text-white">
                  <h3 className="font-label-sm text-xs uppercase mb-md flex items-center gap-2 text-outline-variant">
                    <span className="material-symbols-outlined text-sm">
                      handyman
                    </span>
                    Finalization
                  </h3>
                  {[
                    ['Post-Service Road Test', 'LOGGED'],
                    ['Critical Fastener Torque', 'NM_SPEC'],
                    ['Engineering Certificate', 'verified'],
                  ].map(([item, action], index) => (
                    <div
                      key={item}
                      className={`p-2 text-sm flex justify-between border border-outline mb-2 ${index === 2 ? 'bg-secondary text-white' : ''}`}
                    >
                      <span>{item}</span>
                      <span className="mono-data text-xs">{action}</span>
                    </div>
                  ))}
                </div>
              </div>
            </section>

            <section>
              <SectionTitle number="03" title="Combustion Analysis" />
              <div className="grid grid-cols-1 md:grid-cols-3 gap-gutter">
                {[
                  ['air', 'Ignition & Intake', 'Spark plug analysis, air filter flow test, and throttle body synchronization.', '$110'],
                  ['ev_station', 'Fuel System', 'Injector cleaning, fuel pressure regulation, and tank sediment flush.', '$130'],
                  ['monitor_heart', 'Engine Health', 'Compression testing, leak-down analysis, and borescope cylinder inspection.', '$180'],
                ].map(([icon, title, copy, price], index) => (
                  <button
                    key={title}
                    onClick={() => onBook(title)}
                    className={`border p-md transition-all cursor-pointer flex flex-col justify-between text-left min-h-64 active:scale-[0.98] ${
                      index === 2
                        ? 'border-primary bg-primary text-white'
                        : 'border-outline-variant bg-white hover:shadow-xl'
                    }`}
                  >
                    <div>
                      <div
                        className={`w-12 h-12 flex items-center justify-center mb-md ${index === 2 ? 'bg-secondary' : 'bg-surface-container-high'}`}
                      >
                        <span className="material-symbols-outlined">
                          {icon}
                        </span>
                      </div>
                      <h3 className="font-bold text-lg mb-1 uppercase">
                        {title}
                      </h3>
                      <p
                        className={`text-xs leading-relaxed mb-md ${index === 2 ? 'text-outline-variant' : 'text-outline'}`}
                      >
                        {copy}
                      </p>
                    </div>
                    <p className="mono-data text-xl font-bold">{price}</p>
                  </button>
                ))}
              </div>
            </section>

            <section>
              <SectionTitle number="04" title="Performance Mapping" />
              <div className="border border-outline-variant grid grid-cols-12 overflow-hidden bg-white">
                <div className="col-span-12 md:col-span-5 bg-primary p-md text-white flex flex-col justify-between min-h-[400px]">
                  <div>
                    <span className="font-label-sm text-xs text-secondary uppercase block mb-sm">
                      Laboratory Class
                    </span>
                    <h3 className="font-display-xl text-4xl uppercase mb-md">
                      Dyno Tuning
                    </h3>
                    <p className="text-sm text-outline-variant leading-relaxed mb-xl">
                      High-precision EFI remapping with before/after power
                      graphs, torque curve optimization, and calibration notes.
                    </p>
                  </div>
                  {[
                    ['Class A (1000cc+)', '$450'],
                    ['Class B (600cc)', '$350'],
                    ['Class C (200-400cc)', '$250'],
                  ].map(([label, price]) => (
                    <button
                      key={label}
                      onClick={() => onBook('ECU Tuning')}
                      className="flex justify-between border-b border-outline/30 pb-2 mb-4 text-left hover:text-secondary transition-colors"
                    >
                      <span className="font-label-sm text-xs uppercase">
                        {label}
                      </span>
                      <span className="mono-data">{price}</span>
                    </button>
                  ))}
                </div>
                <div className="col-span-12 md:col-span-7 p-lg grid grid-cols-1 gap-md">
                  <div className="relative overflow-hidden group">
                    <img
                      alt="Dyno Lab"
                      className="w-full h-48 object-cover grayscale group-hover:grayscale-0 transition-all duration-700"
                      src="https://lh3.googleusercontent.com/aida-public/AB6AXuDZVwsTzkmNvecdNT8A1qROWx4z8ftvRRvlphXQO46w4xYXKW8lLH4oTTtf4Vqwfn-YkwOYuG62aS_OU82fQdF9s_dUbQYBQVxQqpTuX4299bgCTj1y5oXxeXHOipud4-9WEMN8NzIlSEwX3GdQ2OC4VRSS5pjM7x3mi7FyEyLV-ZtYk0ka3pY-GuichnVexg1c4np_U6xye4qlDHt4_x7ktzufr26OKEXESpiq7nbtfc6uLdSMvxrpKMWaw8cw9H8BEM0viA6615nr"
                    />
                    <div className="absolute inset-0 bg-primary/20"></div>
                  </div>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-md">
                    {[
                      ['Module_04.A', 'ECU Diagnostics', '$120'],
                      ['Module_04.B', 'ABS Inspection', '$90'],
                    ].map(([code, name, price]) => (
                      <button
                        key={code}
                        onClick={() => onBook(name)}
                        className="border border-outline-variant p-md text-left hover:border-primary transition-all active:scale-95"
                      >
                        <h4 className="font-label-sm text-xs uppercase text-outline mb-1">
                          {code}
                        </h4>
                        <p className="font-bold text-sm uppercase mb-2">
                          {name}
                        </p>
                        <span className="mono-data text-primary font-bold">
                          {price}
                        </span>
                      </button>
                    ))}
                  </div>
                </div>
              </div>
            </section>
          </div>

          <aside className="col-span-12 lg:col-span-3">
            <div className="sticky top-[100px] border border-outline-variant p-md bg-white">
              <div className="flex items-center gap-2 mb-md">
                <span className="material-symbols-outlined text-secondary">
                  inventory_2
                </span>
                <h2 className="font-label-sm text-xs uppercase text-primary">
                  Technical Parts
                </h2>
              </div>
              <div className="space-y-md mb-xl">
                {parts.map((part) => (
                  <div
                    key={part.code}
                    className="group border-b border-outline-variant pb-md last:border-b-0"
                  >
                    <p className="font-label-sm text-[10px] text-outline mb-1">
                      {part.code}
                    </p>
                    <div className="flex justify-between items-start mb-2">
                      <h3 className="font-bold text-sm uppercase">
                        {part.name}
                      </h3>
                      <span className="mono-data text-xs font-bold text-secondary">
                        {part.price}
                      </span>
                    </div>
                    <div className="w-full h-24 bg-surface-container overflow-hidden mb-2">
                      <img
                        alt={part.name}
                        className="w-full h-full object-cover mix-blend-multiply opacity-80 group-hover:opacity-100 transition-opacity"
                        src={part.image}
                      />
                    </div>
                    <button
                      onClick={() => onBook('Custom Fabrication')}
                      className="w-full border border-primary text-[10px] font-bold uppercase py-2 hover:bg-primary hover:text-white transition-all active:scale-[0.98]"
                    >
                      Add to Build
                    </button>
                  </div>
                ))}
              </div>
              <div className="bg-surface-container-high p-md">
                <h3 className="font-label-sm text-[10px] uppercase mb-4 text-outline">
                  Build Estimation
                </h3>
                <div className="flex justify-between mb-2">
                  <span className="text-xs uppercase">Services</span>
                  <span className="mono-data text-xs">$0.00</span>
                </div>
                <div className="flex justify-between mb-4">
                  <span className="text-xs uppercase">Parts</span>
                  <span className="mono-data text-xs">$0.00</span>
                </div>
                <div className="border-t border-outline-variant pt-4 flex justify-between items-baseline">
                  <span className="font-bold text-sm uppercase">Total</span>
                  <span className="mono-data text-lg font-bold text-primary">
                    $0.00
                  </span>
                </div>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </div>
  )
}

function SectionTitle({ number, title }) {
  return (
    <div className="flex items-baseline gap-sm mb-md">
      <span className="font-display-xl text-4xl text-primary opacity-20">
        {number}
      </span>
      <h2 className="font-headline-md text-2xl md:text-3xl text-primary uppercase font-bold">
        {title}
      </h2>
    </div>
  )
}

function SiteFooter() {
  return (
    <footer className="w-full mt-auto bg-primary border-t border-secondary/20">
      <div className="flex flex-col items-center justify-center py-lg px-margin gap-md max-w-screen-2xl mx-auto">
        <div className="font-headline-lg text-3xl text-on-primary uppercase tracking-tighter">
          MAD APE
        </div>
        <div className="flex flex-wrap justify-center gap-lg">
          {[
            'Privacy Policy',
            'Terms of Service',
            'Technical Documentation',
            'Global Support',
          ].map((item) => (
            <span
              key={item}
              className="font-label-sm text-xs uppercase tracking-widest text-on-primary/60 hover:text-on-primary transition-colors hover:underline"
            >
              {item}
            </span>
          ))}
        </div>
        <div className="font-label-sm text-xs uppercase tracking-widest text-on-primary/40 text-center mt-md">
          2026 MAD APE MOTORWORKS. ENGINEERED TO EXCELLENCE.
        </div>
      </div>
    </footer>
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
      <p className="text-on-surface-variant font-body-lg max-w-[28rem] mb-lg">
        This portal section is active. Standard technical information is routed
        directly to our physical workshop coordinators.
      </p>
      <div className="w-16 h-1 bg-secondary"></div>
    </div>
  )
}

export default App
