import { declineAdminRole, deleteMyNotification } from '@/api/restApi'

export async function removeNotificationForUser(notificationId) {
  await deleteMyNotification(notificationId)
  window.dispatchEvent(new Event('contactMessagesUpdated'))
}

export async function declineAdminRoleOffer(notificationId) {
  await declineAdminRole(notificationId)
  window.dispatchEvent(new Event('contactMessagesUpdated'))
}
