import { initializeNavigation } from './ui/navigation.js';
import { initializeMarketingMotion } from './ui/marketing-motion.js';
import { initializeAdminPortal } from './ui/admin-portal.js';
import { initializeExamAuthoring } from './ui/exam-authoring.js';
import { initializeExamStart } from './ui/exam-start.js';
import { initializeExamProctoring } from './ui/exam-proctoring.js';

document.addEventListener('DOMContentLoaded', () => {
    initializeNavigation();
    initializeMarketingMotion();
    initializeAdminPortal();
    initializeExamAuthoring();
    initializeExamStart();
    initializeExamProctoring();
});
