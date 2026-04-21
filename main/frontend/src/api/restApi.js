import { apiFetch } from './http'
import { mapAnimal, mapApplication, mapContactMessage, mapUser } from './mappers'

export async function fetchHomeStats() {
  return apiFetch('/stats', { method: 'GET', token: null })
}

export async function fetchAnimalsCatalog() {
  const data = await apiFetch('/animals', { method: 'GET', token: null })
  const animals = (data.animals || []).map(mapAnimal)
  const takenAnimalIds = data.taken_animal_ids || []
  return { animals, takenAnimalIds }
}

export async function submitContactMessage(payload) {
  await apiFetch('/contact-messages', {
    method: 'POST',
    json: payload,
    token: null,
  })
}

export async function submitApplication(payload) {
  await apiFetch('/applications', { method: 'POST', json: payload })
}

export async function fetchMyApplications() {
  const rows = await apiFetch('/my/applications', { method: 'GET' })
  return rows.map(mapApplication)
}

export async function deleteMyApplication(id) {
  await apiFetch(`/my/applications/${id}`, { method: 'DELETE' })
}

export async function submitSupportMessage(payload) {
  await apiFetch('/support-messages', { method: 'POST', json: payload })
}

export async function fetchMySupportChatUnread() {
  const d = await apiFetch('/my/support-chat/unread-count', { method: 'GET' })
  return typeof d?.unread === 'number' ? d.unread : 0
}

export async function fetchMySupportChat() {
  const rows = await apiFetch('/my/support-chat', { method: 'GET' })
  return Array.isArray(rows) ? rows : []
}

export async function postMySupportChat(payload) {
  return apiFetch('/my/support-chat', { method: 'POST', json: payload })
}

export async function adminListSupportThreads() {
  const rows = await apiFetch('/admin/support-chat/threads', { method: 'GET', admin: true })
  return Array.isArray(rows) ? rows : []
}

export async function adminFetchSupportChat(userId) {
  return apiFetch(`/admin/support-chat/users/${userId}`, { method: 'GET', admin: true })
}

export async function adminPostSupportChat(userId, payload) {
  return apiFetch(`/admin/support-chat/users/${userId}`, { method: 'POST', json: payload, admin: true })
}

export async function fetchMyNotifications() {
  const rows = await apiFetch('/my/notifications', { method: 'GET' })
  return rows.map(mapContactMessage)
}

export async function markMyNotificationsRead() {
  await apiFetch('/my/notifications/mark-read', { method: 'POST' })
}

export async function deleteMyNotification(id) {
  await apiFetch(`/my/notifications/${id}`, { method: 'DELETE' })
}

export async function declineAdminRole(messageId) {
  await apiFetch('/decline-admin-role', {
    method: 'POST',
    json: { message_id: messageId },
  })
}

// --- Admin (Bearer admin token) ---

export async function adminListContactMessages() {
  const rows = await apiFetch('/admin/contact-messages', { method: 'GET', admin: true })
  return rows.map(mapContactMessage)
}

export async function adminUpdateContactMessage(id, status) {
  await apiFetch(`/admin/contact-messages/${id}`, {
    method: 'PATCH',
    json: { status },
    admin: true,
  })
}

export async function adminDeleteContactMessage(id) {
  await apiFetch(`/admin/contact-messages/${id}`, { method: 'DELETE', admin: true })
}

export async function adminCancelAdminRoleOffer(messageId) {
  await apiFetch('/admin/cancel-admin-role-offer', {
    method: 'POST',
    json: { message_id: messageId },
    admin: true,
  })
}

export async function adminListApplications() {
  const rows = await apiFetch('/admin/applications', { method: 'GET', admin: true })
  return rows.map(mapApplication)
}

export async function adminDeleteApplication(id) {
  await apiFetch(`/admin/applications/${id}`, { method: 'DELETE', admin: true })
}

export async function adminApproveApplication(id) {
  const raw = await apiFetch(`/admin/applications/${id}/approve`, { method: 'POST', admin: true })
  return mapApplication(raw)
}

export async function adminCreateAnimal(payload) {
  const raw = await apiFetch('/admin/animals', { method: 'POST', json: payload, admin: true })
  return mapAnimal(raw)
}

export async function adminUpdateAnimal(id, payload) {
  const raw = await apiFetch(`/admin/animals/${id}`, {
    method: 'PATCH',
    json: payload,
    admin: true,
  })
  return mapAnimal(raw)
}

export async function adminDeleteAnimal(id) {
  await apiFetch(`/admin/animals/${id}`, { method: 'DELETE', admin: true })
}

export async function adminListUsers() {
  const rows = await apiFetch('/admin/users', { method: 'GET', admin: true })
  return rows.map(mapUser)
}

export async function adminListActivityLogs() {
  const rows = await apiFetch('/admin/activity-logs', { method: 'GET', admin: true })
  return Array.isArray(rows) ? rows : []
}

export async function adminUpdateUser(id, patch) {
  const raw = await apiFetch(`/admin/users/${id}`, {
    method: 'PATCH',
    json: patch,
    admin: true,
  })
  return mapUser(raw)
}
