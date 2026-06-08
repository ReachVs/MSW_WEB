const PROFILE_STORAGE_KEY = 'msw-profile'

const DEFAULT_PROFILE = {
  firstName: 'Dominic',
  lastName: 'Turner',
  email: 'dominic.turner@madape.com',
  phone: '+1 (555) 123-4567',
  joinDate: '2024-06-15',
}

export function getStoredProfile() {
  try {
    const raw = localStorage.getItem(PROFILE_STORAGE_KEY)
    if (!raw) return DEFAULT_PROFILE

    const parsed = JSON.parse(raw)
    if (!parsed || typeof parsed !== 'object') return DEFAULT_PROFILE

    return {
      ...DEFAULT_PROFILE,
      ...parsed,
    }
  } catch {
    return DEFAULT_PROFILE
  }
}

export function saveStoredProfile(profile) {
  const normalizedProfile = {
    ...DEFAULT_PROFILE,
    ...profile,
  }

  try {
    localStorage.setItem(PROFILE_STORAGE_KEY, JSON.stringify(normalizedProfile))
  } catch {
    // Ignore localStorage write failures.
  }

  return normalizedProfile
}

export function getProfileDisplayName(profile = getStoredProfile()) {
  const fullName = `${profile.firstName || ''} ${profile.lastName || ''}`.trim()
  return fullName || 'Customer'
}

export { DEFAULT_PROFILE, PROFILE_STORAGE_KEY }
