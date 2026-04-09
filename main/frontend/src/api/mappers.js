export function mapContactMessage(m) {
  return {
    id: m.id,
    name: m.name,
    email: m.email,
    message: m.message,
    selectedAnimals: m.selected_animals ?? '',
    sentAt: m.sent_at,
    source: m.source,
    status: m.status,
    read: !!m.is_read,
    moderatedAt: m.moderated_at,
    approvedAt: m.approved_at,
  }
}

export function mapApplication(a) {
  return {
    id: a.id,
    animalName: a.animal_name,
    animalId: a.animal_id,
    animalImage: a.animal_image,
    name: a.applicant_name,
    email: a.applicant_email,
    phone: a.phone,
    address: a.address,
    experience: a.experience,
    submittedAt: a.submitted_at,
  }
}

export function mapUser(u) {
  return {
    id: u.id,
    name: u.name,
    email: u.email,
    blocked: !!u.is_blocked,
    isAdmin: !!u.is_admin,
    isOwner: !!u.is_owner,
    lastLoginAt: u.last_login_at,
    lastLogoutAt: u.last_logout_at,
  }
}

export function mapAnimal(a) {
  return {
    id: a.id,
    name: a.name,
    species: a.species || 'Cits',
    gender: a.gender,
    age: a.age,
    description: a.description,
    image: a.image,
    categoryId: a.category_id,
  }
}
