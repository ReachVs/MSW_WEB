import { useState } from 'react'
import Navbar from './components/Navbar'
import Sidebar from './components/Sidebar'
import BookingModal from './components/BookingModal'
import LoginPage from './components/LoginPage'
import LandingPage from './components/LandingPage'
import GaragePage from './components/GaragePage'
import ServiceHistoryPage from './components/ServiceHistoryPage'
import ProfilePage from './components/ProfilePage'
import RegisterPage from './components/RegisterPage'
import CatalogPage from './components/CatalogPage'
import { getStoredProfile } from './utils/profileStorage'
import './index.css'

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(
    () => !!localStorage.getItem('authToken'),
  )
  const [currentView, setCurrentView] = useState(() =>
    localStorage.getItem('authToken') ? 'garage' : 'landing',
  )
  const [isBookingOpen, setIsBookingOpen] = useState(false)
  const [preSelectedService, setPreSelectedService] = useState('Desmo Service')
  const [profile, setProfile] = useState(() => getStoredProfile())

  // handleLogin and handleRegister now set isAuthenticated and navigate to garage
  const handleLogin = () => {
    setIsAuthenticated(true)
    setCurrentView('garage')
  }

  const handleRegister = () => {
    setIsAuthenticated(true)
    setCurrentView('garage')
  }

  const handleLogout = () => {
    setIsAuthenticated(false)
    setCurrentView('landing')
    localStorage.removeItem('authToken') // Clear token on logout
  }

  const handleBookServiceSubmit = async (bookingData) => {
    // This function now needs to make an API call to the backend
    // to create a new booking.
    const token = localStorage.getItem('authToken')
    if (!token) {
      console.error('Authentication token not found. Cannot submit booking.')
      // Optionally, redirect to login or show an error toast
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
          service_name: bookingData.serviceType,
          customer_name: bookingData.customerName, // Assuming customerName is passed
          customer_email: bookingData.customerEmail, // Assuming customerEmail is passed
          starts_at: bookingData.date, // Assuming date is in a format Laravel accepts
          notes: bookingData.notes || bookingData.bikeName, // Use bikeName as notes for now
          // Add other fields as required by your StoreBookingRequest
        }),
      })

      if (!response.ok) {
        const errorData = await response.json()
        console.error('Booking submission failed:', errorData)
        // Handle error (e.g., show toast)
        return
      }

      const newBooking = await response.json()
      console.log('Booking submitted successfully:', newBooking)

      setIsBookingOpen(false)
      // Redirect to Service History to see the newly added log (or garage for active)
      setCurrentView('history') // Or 'garage' if you want to see it in active services
    } catch (error) {
      console.error('Network error during booking submission:', error)
      // Handle network error
    }
  }

  const triggerBooking = (defaultService = 'Desmo Service') => {
    setPreSelectedService(defaultService)

    if (!isAuthenticated) {
      setCurrentView('login')
      return
    }

    setCurrentView('catalog')
  }

  // Render view depending on navigation routing
  const renderViewContent = () => {
    const protectedViews = ['garage', 'history', 'profile']

    if (!isAuthenticated && currentView === 'register') {
      return (
        <RegisterPage
          onRegisterSuccess={handleRegister}
          onNavigate={setCurrentView}
        />
      )
    }

    if (
      !isAuthenticated &&
      (currentView === 'login' || protectedViews.includes(currentView))
    ) {
      return (
        <LoginPage onLoginSuccess={handleLogin} onNavigate={setCurrentView} />
      )
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
        return <GaragePage onAddService={() => triggerBooking()} /> // Pass triggerBooking as onAddService
      case 'history':
        return <ServiceHistoryPage />
      case 'catalog':
        return (
          <CatalogPage
            onBook={() => {
              if (isAuthenticated) {
                setCurrentView('garage')
              } else {
                setCurrentView('login')
              }
            }}
          />
        )
      case 'profile':
        return isAuthenticated ? (
          <ProfilePage
            onLogout={handleLogout}
            profile={profile}
            onProfileSave={setProfile}
          />
        ) : (
          <LoginPage onLoginSuccess={handleLogin} onNavigate={setCurrentView} />
        )
      case 'login':
        return isAuthenticated ? (
          <GaragePage />
        ) : (
          <LoginPage onLoginSuccess={handleLogin} onNavigate={setCurrentView} />
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
      {!isAuthenticated &&
      (currentView === 'login' || currentView === 'register') ? (
        renderViewContent()
      ) : (
        <>
          {/* Main sidebar for authenticated screens */}
          {isAuthenticated && (
            <Sidebar
              currentView={currentView}
              onNavigate={setCurrentView}
              onBookService={() => triggerBooking()}
              profile={profile}
            />
          )}

          {/* Top Navbar */}
          <Navbar
            isAuthenticated={isAuthenticated}
            currentView={currentView}
            onLogin={() => setCurrentView('login')}
            onRegister={() => setCurrentView('register')}
            onLogout={handleLogout}
            onNavigate={setCurrentView}
          />

          {/* Main app block */}
          <div
            className={`flex flex-col flex-1 ${isAuthenticated ? 'ml-0 md:ml-64' : 'ml-0'}`}
          >
            {/* Dynamic page content */}
            <main className="flex-grow flex flex-col">
              {renderViewContent()}
            </main>
            {!isAuthenticated && <SiteFooter />}
          </div>

          {isAuthenticated && (
            <BookingModal
              isOpen={isBookingOpen}
              onClose={() => setIsBookingOpen(false)}
              onSubmit={handleBookServiceSubmit}
              defaultService={preSelectedService}
            />
          )}
        </>
      )}
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
