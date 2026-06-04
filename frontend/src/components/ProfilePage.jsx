import { useState } from 'react'

export default function ProfilePage({ onLogout }) {
  const [user, setUser] = useState({
    firstName: 'Dominic',
    lastName: 'Turner',
    email: 'dominic.turner@madape.com',
    phone: '+1 (555) 123-4567',
    membershipTier: 'PRO',
    joinDate: '2024-06-15',
  })

  const [savedSettings, setSavedSettings] = useState({
    emailNotifications: true,
    smsAlerts: false,
    liveTracking: true,
    diagnosticShare: true,
  })

  const [editMode, setEditMode] = useState(false)
  const [tempUser, setTempUser] = useState(user)
  const [saveMessage, setSaveMessage] = useState('')

  const handleSaveProfile = () => {
    setUser(tempUser)
    setSaveMessage('Profile updated successfully')
    setEditMode(false)
    setTimeout(() => setSaveMessage(''), 3000)
  }

  const handleCancel = () => {
    setTempUser(user)
    setEditMode(false)
  }

  const toggleSetting = (key) => {
    setSavedSettings((prev) => ({
      ...prev,
      [key]: !prev[key],
    }))
  }

  return (
    <div className="flex-grow p-lg overflow-y-auto">
      <div className="max-w-4xl mx-auto space-y-lg">
        {/* Header */}
        <div>
          <h1 className="font-display-xl text-3xl md:text-4xl text-primary uppercase font-black tracking-tighter mb-2">
            ACCOUNT SETTINGS
          </h1>
          <p className="text-on-surface-variant font-body-md text-sm">
            Manage your profile information, preferences, and session controls.
          </p>
        </div>

        {/* Success Message */}
        {saveMessage && (
          <div className="p-md bg-primary text-on-primary border border-secondary flex items-center gap-2">
            <span className="material-symbols-outlined text-lg">check_circle</span>
            <span className="font-body-md text-sm">{saveMessage}</span>
          </div>
        )}

        <div className="space-y-lg">
          {/* Profile Information Section */}
          <section className="bg-white border border-outline-variant p-lg">
            <div className="flex items-center justify-between mb-lg">
              <h2 className="font-headline-lg text-2xl text-primary uppercase font-black">
                Profile Information
              </h2>
              {!editMode && (
                <button
                  onClick={() => setEditMode(true)}
                  className="flex items-center gap-2 px-md py-sm bg-primary text-on-primary font-label-sm text-xs uppercase tracking-widest hover:bg-secondary transition-all"
                >
                  <span className="material-symbols-outlined text-sm">edit</span>
                  Edit Profile
                </button>
              )}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-lg">
              {editMode ? (
                <>
                  <div>
                    <label className="font-label-sm text-xs uppercase tracking-widest text-outline mb-2 block font-bold">
                      First Name
                    </label>
                    <input
                      type="text"
                      value={tempUser.firstName}
                      onChange={(e) =>
                        setTempUser({ ...tempUser, firstName: e.target.value })
                      }
                      className="w-full px-sm py-2 border border-outline-variant bg-surface text-on-surface font-body-md text-sm focus:outline-none focus:border-secondary"
                    />
                  </div>

                  <div>
                    <label className="font-label-sm text-xs uppercase tracking-widest text-outline mb-2 block font-bold">
                      Last Name
                    </label>
                    <input
                      type="text"
                      value={tempUser.lastName}
                      onChange={(e) =>
                        setTempUser({ ...tempUser, lastName: e.target.value })
                      }
                      className="w-full px-sm py-2 border border-outline-variant bg-surface text-on-surface font-body-md text-sm focus:outline-none focus:border-secondary"
                    />
                  </div>

                  <div>
                    <label className="font-label-sm text-xs uppercase tracking-widest text-outline mb-2 block font-bold">
                      Email
                    </label>
                    <input
                      type="email"
                      value={tempUser.email}
                      onChange={(e) =>
                        setTempUser({ ...tempUser, email: e.target.value })
                      }
                      className="w-full px-sm py-2 border border-outline-variant bg-surface text-on-surface font-body-md text-sm focus:outline-none focus:border-secondary"
                    />
                  </div>

                  <div>
                    <label className="font-label-sm text-xs uppercase tracking-widest text-outline mb-2 block font-bold">
                      Phone
                    </label>
                    <input
                      type="tel"
                      value={tempUser.phone}
                      onChange={(e) =>
                        setTempUser({ ...tempUser, phone: e.target.value })
                      }
                      className="w-full px-sm py-2 border border-outline-variant bg-surface text-on-surface font-body-md text-sm focus:outline-none focus:border-secondary"
                    />
                  </div>
                </>
              ) : (
                <>
                  <div>
                    <label className="font-label-sm text-xs uppercase tracking-widest text-outline mb-1 block font-bold">
                      Name
                    </label>
                    <p className="font-body-md text-sm text-on-surface">
                      {user.firstName} {user.lastName}
                    </p>
                  </div>

                  <div>
                    <label className="font-label-sm text-xs uppercase tracking-widest text-outline mb-1 block font-bold">
                      Email
                    </label>
                    <p className="font-body-md text-sm text-on-surface break-all">
                      {user.email}
                    </p>
                  </div>

                  <div>
                    <label className="font-label-sm text-xs uppercase tracking-widest text-outline mb-1 block font-bold">
                      Phone
                    </label>
                    <p className="font-body-md text-sm text-on-surface">
                      {user.phone}
                    </p>
                  </div>

                  <div>
                    <label className="font-label-sm text-xs uppercase tracking-widest text-outline mb-1 block font-bold">
                      Membership
                    </label>
                    <p className="font-body-md text-sm text-secondary font-bold">
                      {user.membershipTier} MEMBER
                    </p>
                  </div>
                </>
              )}
            </div>

            {editMode && (
              <div className="flex gap-md mt-lg pt-lg border-t border-outline-variant justify-end">
                <button
                  onClick={handleCancel}
                  className="px-md py-sm border border-outline-variant text-primary font-label-sm text-xs uppercase tracking-widest hover:bg-surface-container transition-all"
                >
                  Cancel
                </button>
                <button
                  onClick={handleSaveProfile}
                  className="px-md py-sm bg-secondary text-on-secondary font-label-sm text-xs uppercase tracking-widest hover:bg-primary transition-all"
                >
                  Save Changes
                </button>
              </div>
            )}
          </section>

          {/* Notification Preferences */}
          <section className="bg-white border border-outline-variant p-lg">
            <h2 className="font-headline-lg text-2xl text-primary uppercase font-black mb-lg">
              Notification Preferences
            </h2>

            <div className="space-y-md">
              {[
                {
                  key: 'emailNotifications',
                  label: 'Email Notifications',
                  description: 'Receive service updates and alerts via email',
                },
                {
                  key: 'smsAlerts',
                  label: 'SMS Alerts',
                  description:
                    'Get critical alerts about your vehicles via text',
                },
                {
                  key: 'liveTracking',
                  label: 'Live Workshop Updates',
                  description:
                    'Receive real-time updates on service progress',
                },
                {
                  key: 'diagnosticShare',
                  label: 'Diagnostic Data Sharing',
                  description:
                    'Share telemetry data with our diagnostic systems',
                },
              ].map((item) => (
                <label
                  key={item.key}
                  className="flex items-start gap-3 p-md bg-surface-container-low hover:bg-surface-container transition-colors cursor-pointer"
                >
                  <input
                    type="checkbox"
                    checked={savedSettings[item.key]}
                    onChange={() => toggleSetting(item.key)}
                    className="w-5 h-5 text-secondary border-outline-variant mt-1 cursor-pointer"
                  />
                  <div className="flex-1">
                    <div className="font-headline-md text-sm text-primary font-bold">
                      {item.label}
                    </div>
                    <p className="font-body-md text-xs text-on-surface-variant mt-1">
                      {item.description}
                    </p>
                  </div>
                </label>
              ))}
            </div>
          </section>

          {/* Account Actions */}
          <section className="bg-white border border-outline-variant p-lg">
            <h2 className="font-headline-lg text-2xl text-primary uppercase font-black mb-lg">
              Account Actions
            </h2>

            <div className="space-y-md">
              <button className="w-full p-md border border-outline-variant bg-white hover:bg-surface-container-low transition-colors flex items-center justify-between group">
                <div className="text-left">
                  <div className="font-headline-md text-sm text-primary font-bold">
                    Change Password
                  </div>
                  <p className="font-body-md text-xs text-on-surface-variant mt-1">
                    Update your access code for enhanced security
                  </p>
                </div>
                <span className="material-symbols-outlined text-primary group-hover:translate-x-1 transition-transform">
                  arrow_forward
                </span>
              </button>

              <button className="w-full p-md border border-outline-variant bg-white hover:bg-surface-container-low transition-colors flex items-center justify-between group">
                <div className="text-left">
                  <div className="font-headline-md text-sm text-primary font-bold">
                    Download Data Export
                  </div>
                  <p className="font-body-md text-xs text-on-surface-variant mt-1">
                    Export your service history and personal data
                  </p>
                </div>
                <span className="material-symbols-outlined text-primary group-hover:translate-x-1 transition-transform">
                  download
                </span>
              </button>

              <button
                onClick={onLogout}
                className="w-full p-md border-2 border-error bg-error-container text-on-error-container hover:bg-error hover:text-error-container transition-colors flex items-center justify-between font-bold"
              >
                <div className="text-left">
                  <div className="font-headline-md text-sm font-bold">
                    Logout Session
                  </div>
                  <p className="font-body-md text-xs mt-1 opacity-80">
                    End your current telemetry session
                  </p>
                </div>
                <span className="material-symbols-outlined">logout</span>
              </button>
            </div>
          </section>

          {/* Footer Info */}
          <div className="text-center py-lg border-t border-outline-variant">
            <p className="font-mono text-[10px] text-on-surface-variant uppercase tracking-widest">
              Member Since {new Date(user.joinDate).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
              })}
            </p>
            <p className="font-mono text-[9px] text-outline mt-2">
              Version: 2026.06.04-Vite-React
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
