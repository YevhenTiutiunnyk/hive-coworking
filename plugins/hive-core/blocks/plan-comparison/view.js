/**
 * WordPress dependencies
 */
import { getContext, store } from '@wordpress/interactivity';

store( 'hive/plans', {
	state: {
		get isMonthly() {
			return getContext().billing === 'monthly';
		},
		get isYearly() {
			return getContext().billing === 'yearly';
		},
	},
	actions: {
		showMonthly() {
			getContext().billing = 'monthly';
		},
		showYearly() {
			getContext().billing = 'yearly';
		},
	},
} );
