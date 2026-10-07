/**
 * Test-only credentials for the seeded demo member on the local wp-env tests site.
 */
module.exports = {
	MEMBER_LOGIN: 'demo',
	MEMBER_PASSWORD: process.env.E2E_DEMO_PASSWORD || 'hive-e2e-demo',
};
