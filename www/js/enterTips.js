$(document).ready(function() {
	$('input[type=text].tipsToRight').bt({
		width: '300px',
		trigger: ['focus', 'blur'],
		positions: ['right'],
		fill: '#f9f9f9',
		padding: '10px 15px',
		cornerRadius: 1,
		clickAnywhereToClose: true,
		closeWhenOthersOpen: true,
		shadow: true,
		shadowOffsetX: 5,
		shadowOffsetY: 5,
		shadowColor: '#999999',
		shadowBlur: 10,
		cssStyles: {fontFamily: 'Arial, Helvetica, sans-serif', fontSize: '14px', lineHeight: '18px'}
	});
	$('input[type=text].tipsToTop').bt({
		width: '240px',
		trigger: ['focus', 'blur'],
		positions: ['top'],
		fill: '#f9f9f9',
		padding: '10px 15px',
		cornerRadius: 1,
		clickAnywhereToClose: true,
		closeWhenOthersOpen: true,
		shadow: true,
		shadowOffsetX: 5,
		shadowOffsetY: 5,
		shadowColor: '#999999',
		shadowBlur: 10,
		cssStyles: {fontFamily: 'Arial, Helvetica, sans-serif', fontSize: '14px', lineHeight: '18px'}
	});
});