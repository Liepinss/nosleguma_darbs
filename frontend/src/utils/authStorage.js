import { getAuthToken } from '@/api/http'
import {
  clearUserSession,
  getStoredUser,
  logoutUser,
  refreshSessionUser,
} from '@/api/authApi'

export function isUserLoggedIn() {
  return !!getAuthToken()
}

export function isAdminLoggedIn() {
  return !!getAuthToken() && !!getStoredUser()?.isAdmin
}

/** @deprecated local sessions — use deriveActiveSessionsFromUsers for admin UI */
export function getActiveSessions() {
  return []
}

export function setActiveSessions() {
  // no-op: server tracks last_login_at / last_logout_at
}

/**
 * @param {import('@/api/mappers').mapUser extends Function ? never : any} users
 */
export function deriveActiveSessionsFromUsers(users) {
  return users
    .filter(
      (u) =>
        u.lastLoginAt &&
        (!u.lastLogoutAt || new Date(u.lastLoginAt) > new Date(u.lastLogoutAt)),
    )
    .map((u) => ({
      email: u.email,
      name: u.name,
      loginAt: u.lastLoginAt,
    }))
}

export function registerUserSession() {
  // Server updates last_login_at on login
}

export function removeUserSession() {
  // no-op
}

export async function recordUserLogout() {
  await logoutUser()
}

export function getUsers() {
  return []
}

export function saveUsers() {
  // no-op
}

export function isEmailBlocked() {
  return false
}

export async function setUserBlocked(userId, blocked) {
  const { adminUpdateUser } = await import('@/api/restApi')
  await adminUpdateUser(userId, { is_blocked: blocked })
}

export async function revokeAdminRoleForUser(userId) {
  const { adminUpdateUser } = await import('@/api/restApi')
  await adminUpdateUser(userId, { is_admin: false })
}

/**
 * @returns {Promise<boolean>} true if user was logged out due to block
 */
export async function clearLoggedInUserIfBlocked() {
  if (!getAuthToken()) return false
  try {
    const u = await refreshSessionUser()
    if (u.blocked) {
      await logoutUser()
      return true
    }
  } catch {
    clearUserSession()
    return true
  }
  return false
}

export function getCurrentUserEmailSync() {
  return getStoredUser()?.email || ''
}
