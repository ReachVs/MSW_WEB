export default function Sidebar({ currentView, onNavigate, onBookService }) {
  const menuItems = [
    { id: 'landing', label: 'Home', icon: 'home' },
    { id: 'garage', label: 'My Garage', icon: 'motorcycle' },
    { id: 'catalog', label: 'Catalog Service', icon: 'settings_suggest' },
    { id: 'history', label: 'Service History', icon: 'history' },
    { id: 'profile', label: 'Account', icon: 'account_circle' },
  ]

  return (
    <aside className="fixed left-0 top-0 h-screen w-64 border-r border-outline-variant bg-surface flex flex-col py-md px-sm shrink-0 z-40">
      {/* Brand Header */}
      <div className="px-sm mb-lg">
        <div className="font-display-xl text-[24px] font-black text-primary leading-tight tracking-tight">
          MAD APE
        </div>
        <div className="font-label-sm text-[10px] uppercase tracking-widest text-on-surface-variant opacity-60">
          PRECISION MOTORWORKS
        </div>
      </div>

      {/* Book Service Action */}
      <button
        onClick={onBookService}
        className="mx-sm mb-md bg-secondary text-on-primary py-sm px-md font-label-sm tracking-widest hover:bg-primary transition-all active:scale-95 text-center uppercase tracking-[0.2em]"
      >
        BOOK SERVICE
      </button>

      {/* Navigation Menu */}
      <nav className="flex-grow flex flex-col space-y-xs">
        {menuItems.map((item) => {
          const isActive = currentView === item.id
          return (
            <button
              key={item.id}
              onClick={() => onNavigate(item.id)}
              className={`flex items-center py-sm pl-5 font-label-sm text-xs uppercase tracking-widest transition-all active:translate-x-1 group ${
                isActive
                  ? 'text-primary font-bold border-l-4 border-secondary bg-surface-container'
                  : 'text-on-surface-variant hover:bg-surface-container-high'
              }`}
            >
              <span
                className={`material-symbols-outlined mr-md text-lg ${
                  isActive
                    ? 'text-secondary'
                    : 'opacity-70 group-hover:opacity-100'
                }`}
              >
                {item.icon}
              </span>
              {item.label}
            </button>
          )
        })}
      </nav>

      {/* Client Profile Footer */}
      <div className="mt-auto px-sm pt-lg border-t border-outline-variant">
        <div className="bg-surface-container-high p-sm flex items-center space-x-sm hairline-border">
          <img
            alt="User Profile Avatar"
            className="w-10 h-10 object-cover border border-outline-variant grayscale hover:grayscale-0 transition-all duration-300"
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAQmUO03Kwf962DfSmx1-c1j5lJrq4Fxx57pTVGgY9XA1PszGQZ0-jQklE-kOPBCR46pF2vG_wbmtM_nyd3Y5L4_a6_N0U4bIXHg-azPleHifsA55JRbK1CvauKKKEv3apKf0s6K6qk1R4GdffThaqdjYLeMubCn0tCs1GStvAQGgKBfT8Vyyc4YrQ55PdBfgzp3cdnSIgwSYP6oLRzZB9mdR5Co_dkAMNi-h_67-Rvfe_BeBiImx-mnPJiwwE7zAOa-evuGp-xxHNu"
          />
          <div className="overflow-hidden">
            <p className="font-label-sm text-[9px] leading-none mb-1 opacity-60">
              CLIENT
            </p>
            <p className="font-bold text-primary truncate w-32 uppercase text-xs">
              Dominic T.
            </p>
          </div>
        </div>
      </div>
    </aside>
  )
}
