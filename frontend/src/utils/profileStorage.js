const PROFILE_STORAGE_KEY = 'msw-profile'

const DEFAULT_PROFILE = {
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  joinDate: '2024-06-15',
  avatar: null,
}

export function getStoredProfile(email = null) {
  try {
    const userEmail = email || localStorage.getItem('activeUserEmail')
    const key = userEmail
      ? `${PROFILE_STORAGE_KEY}-${userEmail}`
      : PROFILE_STORAGE_KEY
    const raw = localStorage.getItem(key)
    if (!raw) {
      return {
        ...DEFAULT_PROFILE,
        email: userEmail || DEFAULT_PROFILE.email,
      }
    }

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

export function saveStoredProfile(profile, email = null) {
  const userEmail =
    email || profile.email || localStorage.getItem('activeUserEmail')
  const key = userEmail
    ? `${PROFILE_STORAGE_KEY}-${userEmail}`
    : PROFILE_STORAGE_KEY

  const normalizedProfile = {
    ...DEFAULT_PROFILE,
    ...profile,
  }

  try {
    localStorage.setItem(key, JSON.stringify(normalizedProfile))
    if (userEmail) {
      localStorage.setItem('activeUserEmail', userEmail)
    }
  } catch {
    // Ignore localStorage write failures.
  }

  return normalizedProfile
}

export function clearStoredProfile() {
  try {
    localStorage.removeItem('activeUserEmail')
  } catch {
    // Ignore localStorage write failures.
  }

  return DEFAULT_PROFILE
}

export function getProfileDisplayName(profile = getStoredProfile()) {
  const fullName = `${profile.firstName || ''} ${profile.lastName || ''}`.trim()
  return fullName || 'Customer'
}

export { DEFAULT_PROFILE, PROFILE_STORAGE_KEY }
