import { initializeNavigation } from './ui/navigation.js';
import { initializePortalModal } from './ui/portal-modal.js';
import { initializeMarketingMotion } from './ui/marketing-motion.js';
import { initializeAdminPortal } from './ui/admin-portal.js';
import { initializeExamAuthoring } from './ui/exam-authoring.js';

document.addEventListener('DOMContentLoaded', () => {
    initializeNavigation();
    initializePortalModal();
    initializeMarketingMotion();
    initializeAdminPortal();
    initializeExamAuthoring();
});
