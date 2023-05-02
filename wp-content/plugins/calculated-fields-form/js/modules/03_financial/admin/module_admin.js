fbuilderjQuery = (typeof fbuilderjQuery != 'undefined' ) ? fbuilderjQuery : jQuery;
fbuilderjQuery[ 'fbuilder' ] = fbuilderjQuery[ 'fbuilder' ] || {};
fbuilderjQuery[ 'fbuilder' ][ 'modules' ] = fbuilderjQuery[ 'fbuilder' ][ 'modules' ] || {};

fbuilderjQuery[ 'fbuilder' ][ 'modules' ][ 'financial' ] = {
	'tutorial' : 'https://cff.dwbooster.com/documentation#financial-module',
	'toolbars'		: {
		'finance' : {
			'label' : 'Financial functions',
			'buttons' : [
							{
								"value" : "CALCULATEPAYMENT",
								"code"  : "CALCULATEPAYMENT(",
								"tip"   : "<p><strong>Calculate the Financed Payment Amount</strong></p> \
										  <p>Three parameters: amount, months, interest rate (percent)</p> \
								          <p>Ex: <strong>CALCULATEPAYMENT(25000, 60, 5.25)</strong><br /> \
										  Result: <strong>474.65</strong></p>"
							},

							{
								"value" : "CALCULATEAMOUNT",
								"code"  : "CALCULATEAMOUNT(",
								"tip"   : "<p><strong>Calculate the Financed Amount</strong></p> \
										  <p>Three parameters: months, interest rate (percent), payment </p> \
								          <p>Ex: <strong>CALCULATEAMOUNT(60, 5.25, 474.65)</strong><br /> \
										  Result: <strong>25000.02</strong></p>"
							},

							{
								"value" : "CALCULATEMONTHS",
								"code"  : "CALCULATEMONTHS(",
								"tip"   : "<p><strong>Calculate the Months Financed</strong></p>\
										  <p>Three parameters: amount, interest rate (percent), payment</p> \
								          <p>Ex: <strong>CALCULATEMONTHS(25000, 5.25, 474.65)</strong><br /> \
										  Result: <strong>60</strong></p>"
							},

							{
								"value" : "CALCULATEINTEREST",
								"code"  : "CALCULATEINTEREST(",
								"tip"   : "<p><strong>Calculate the Financed Interest Rate</strong></p>\
										  <p>Three parameters: amount, months, payment</p>\
								          <p>Ex: <strong>CALCULATEINTEREST(25000, 60, 474.65)</strong><br /> \
										  Result: <strong>5.25</strong></p>"
							},

							{
								"value" : "CALCULATEACCRUEDINTEREST",
								"code"  : "CALCULATEACCRUEDINTEREST(",
								"tip"   : "<p><strong>Calculate the Accrued Interest</strong></p>\
										  <p>If your money is in a bank account accruing interest, how much does it earn over x months? Three parameters: principle amount, months, interest rate (percent)</p>\
								          <p>Ex: <strong>CALCULATEACCRUEDINTEREST(25000, 60, 5.25)</strong><br /> \
										  Result: <strong>7485.806648756854</strong></p>"
							},

							{
								"value" : "CALCULATEAMORTIZATION",
								"code"  : "CALCULATEAMORTIZATION(",
								"tip"   : "<p><strong>Create Amortization Schedule</strong></p>\
										  <p>Create an amortization schedule. The result should be an array the length the number of months. Each entry is an object. Four parameters: principle amount, months, interest rate (percent), start date (optional Date object)</p>\
								          <p>Ex: <strong>CALCULATEAMORTIZATION(25000, 60, 5.25, new Date(2011,11,20) )</strong><br /> \
										  Result: <br /> \
										  <strong> \
											[ <br /> \
												&nbsp;&nbsp;{ <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;principle: 24634.725 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;interest: 109.375 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;payment: 474.65 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;paymentToPrinciple: 365.275 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;paymentToInterest: 109.375 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;date: Tue Dec 20 2011 00:00:00 GMT+0100 (Romance Daylight Time) <br /> \
												&nbsp;&nbsp;}, <br /> \
												&nbsp;&nbsp;{ <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;principle: 24267.851921874997 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;interest: 217.151921875 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;payment: 474.65 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;paymentToPrinciple: 366.873078125 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;paymentToInterest: 107.776921875 <br /> \
													&nbsp;&nbsp;&nbsp;&nbsp;date: Fri Jan 20 2012 00:00:00 GMT+0100 (Romance Daylight Time) <br /> \
												&nbsp;&nbsp;}, <br /> \
												... <br /> \
											] \
										  </strong></p>"
							},

							{
								"value" : "PRESENTVALUE",
								"code"  : "PRESENTVALUE(",
								"tip"   : "<p><strong>Returns the present value of an investment</strong></p>\
										  <p>The present value is the total amount that a series of future payments is worth now. It requires three parameters:  The interest rate per period, the total number of payment periods, the payment made each period and cannot change over the life of the annuity, and two optional parameters: The future value of the loan or investment and the due parameter (0 - the payment is due at the end of the period; 1 - the payment is due at the beginning of the period.) </p>\
								          <p>Ex: <strong>PRESENTVALUE(0.08,5,100)</strong><br /> \
										  Result: <strong>399.27</strong></p>"
							},

							{
								"value" : "FUTUREVALUE",
								"code"  : "FUTUREVALUE(",
								"tip"   : "<p><strong>Returns the future value of an investment based on an interest rate and a constant payment schedule</strong></p>\
										  <p>The future value of an investment based on an interest rate and a constant payment schedule. Five parameters:  The interest rate for the investment, the number of payments for the annuity, the  amount of the payment made each period, the present value of the payments (if this parameter is omitted, it assumes to be 0),  parameter that indicates when the payments are due (if this parameter is omitted, it assumes to be 0. The possible values are: 0 - Payments are due at the end of the period, 1 - Payments are due at the beginning of the period)</p>\
								          <p>Ex: <strong>FUTUREVALUE(7.5/12,24,-250,-5000,1)</strong></p>"
							},

							{
								"value" : "NPER",
								"code"  : "NPER(",
								"tip"   : "<p><strong>Returns the number of periods for an investment based on periodic, constant payments and a constant interest rate</strong></p><p><strong>Rate</strong> is the periodic interest rate (%).<br><strong>PMT</strong> The payment made each period; it cannot change over the life of the annuity. Typically, pmt contains principal and interest but no other fees or taxes.<br><strong>PV</strong> The present value, or the lump-sum amount that a series of future payments is worth right now.<br><strong>FV</strong> (optional) The future value, or a cash balance you want to attain after the last payment is made. If fv is omitted, it is assumed to be 0 (the future value of a loan, for example, is 0).<br><strong>Type</strong> (optional) The number 0 or 1 and indicates when payments are due.</p><p>Ex: <strong>NPER(12/12, -100, -1000, 10000, 1)</strong><br>Result: <strong>59.67386567429457</strong></p>"
							},

							{
								"value" : "PMT",
								"code"  : "PMT(",
								"tip"   : "<p><strong>Returns the periodic payment for an annuity with constant interest rates</strong></p><p><strong>Rate</strong> is the periodic interest rate (%).<br><strong>NPer</strong> is the number of periods in which annuity is paid.<br><strong>PV</strong> is the present value (cash value) in a sequence of payments.<br><strong>FV</strong> (optional) is the desired value (future value) to be reached at the end of the periodic payments.<br><strong>Type</strong> (optional) is the due date for the periodic payments. Type=1 is payment at the beginning and Type=0 is payment at the end of each period.</p><p>Ex: <strong>PMT(1.99/12,36,25000)</strong><br>Result: <strong>-715.96</strong></p>"
							},

							{
								"value" : "IPMT",
								"code"  : "IPMT(",
								"tip"   : "<p><strong>Calculates the periodic amortizement for an investment with regular payments and a constant interest rate</strong></p><p><strong>Rate</strong> is the periodic interest rate (%).<br><strong>Period</strong> is the period, for which the compound interest is calculated.<br><strong>NPer</strong> is the number of periods in which annuity is paid.<br><strong>PV</strong> is the present value (cash value) in a sequence of payments.<br><strong>FV</strong> (optional) is the desired value (future value) to be reached at the end of the periods.<br><strong>Type</strong> (optional) is the due date for the periodic payments. Type=1 is payment at the beginning and Type=0 is payment at the end of each period.</p><p>Ex: <strong>IPMT(5,5,7,15000)</strong><br>Result: <strong>-352.9735</strong></p>"
							},

							{
								"value" : "PPMT",
								"code"  : "PPMT(",
								"tip"   : "<p><strong>Returns for a given period the payment on the principal for an investment that is based on periodic and constant payments and a constant interest rate</strong></p><p><strong>Rate</strong> is the periodic interest rate (%).<br><strong>Period</strong> is the amortizement period. P = 1 for the first and P = NPer for the last period.<br><strong>NPer</strong> is the number of periods in which annuity is paid.<br><strong>PV</strong> is the present value (cash value) in a sequence of payments.<br><strong>FV</strong> (optional) is the desired value (future value) to be reached at the end of the periods.<br><strong>Type</strong> (optional) is the due date for the periodic payments. Type=1 is payment at the beginning and Type=0 is payment at the end of each period.</p><p>Ex: <strong>PPMT(8.75/12,1,36,5000,8000,1)</strong><br>Result: <strong>-350.99</strong></p>"
							},

							{
								"value" : "PVIF",
								"code"  : "PVIF(",
								"tip"   : "<p><strong>Calculates the today value per currency received at a future date</strong></p><p><strong>Rate</strong> is interest rate per period (%).<br><strong>Periods</strong> is the number of periods.</p><p>Ex: <strong>PVIF(5,10)</strong><br>Result: <strong>0.6139</strong></p>"
							},

							{
								"value" : "FVIFA",
								"code"  : "FVIFA(",
								"tip"   : "<p><strong>Calculates the future value interest factor of annuity</strong></p><p><strong>Rate</strong> is interest rate per period (%).<br><strong>Periods</strong> is the number of periods.</p><p>Ex: <strong>FVIFA(5,17)</strong><br>Result: <strong>25.8404</strong></p>"
							},

							{
								"value" : "XNPV",
								"code"  : "XNPV(",
								"tip"   : "<p><strong>Calculates the capital value (net present value)for a list of payments which take place on different dates.</strong></p><p><strong>Rate</strong> is the internal rate of return for the payments (%).<br><strong>Values</strong> and <strong>Dates</strong> refer to a series of payments and the series of associated date values. The first pair of dates defines the start of the payment plan. All other date values must be later, but need not be in any order. The series of values must contain at least one negative and one positive value (receipts and deposits).</p><p>Ex: <strong>XNPV(9,[-10000,2750,4250,3250,2750],[&quot;1/1/2008&quot;, &quot;3/1/2008&quot;, &quot;10/30/2008&quot;, &quot;2/15/2009&quot;, &quot;4/1/2009&quot;])</strong><br>Result: <strong>2068.65</strong></p>"
							},

							{
								"value" : "NPV",
								"code"  : "NPV(",
								"tip"   : "<p><strong>Calculates the net present value of an investment based on a discount rate and a series of periodic cash flows.</strong></p><p><strong>Rate</strong> the discount rate of the investment over one period (%).<br><strong>Values</strong> array of values representing payments and income</p><p>Ex: <strong>NPV(10,[-10000,3000,4200,6800])</strong><br>Result: <strong>1188.44</strong></p>"
							},

							{
								"value" : "IRR",
								"code"  : "IRR(",
								"tip"   : "<p><strong>Internal Rate of Return (IRR)</strong></p><p>The Internal Rate of Return (IRR) is a financial function that returns the internal rate of return (IRR) for a series of cash flows that occur at regular intervals.</p><p><strong>Values</strong> Array of cash values.<br><strong>Guess</strong> (optional) [optional] An estimate for expected IRR.<p>Ex: <strong>IRR([-10000,2000,2500,5000,1000])</strong><br>Result: <strong>0.01996530806985132</strong></p>"
							},

							{
								"value" : "XIRR",
								"code"  : "XIRR(",
								"tip"   : "<p><strong>Calculates the internal rate of return for a list of payments which take place on different dates.</strong></p><p> The calculation is based on a 365 days per year basis, ignoring leap years.</p><p><strong>Values</strong> and <strong>Dates</strong> refer to a series of payments and the series of associated date values. The first pair of dates defines the start of the payment plan. All other date values must be later, but need not be in any order. The series of values must contain at least one negative and one positive value (receipts and deposits).<br><strong>Guess</strong> (optional) is a guess that can be input for the internal rate of return (%). The default is 10</p><p>Ex: <strong>XIRR([-10000,2000,2500,5000,1000],[&quot;01/01/2001&quot;, &quot;01/02/2001&quot;, &quot;03/15/2001&quot;, &quot;05/12/2001&quot;, &quot;08/10/2001&quot;],10)</strong><br>Result: <strong>0.1948184751</strong></p>"
							},

							{
								"value" : "MIRR",
								"code"  : "MIRR(",
								"tip"   : "<p><strong>Calculates the modified internal rate of return on an investment based on a series of periodic cash flows and the difference between the interest rate paid on financing versus the return received on reinvested income.</strong></p><p><strong>Values</strong> array of values containing the income or payments associated with the investment.<br><strong>Financing Rate</strong> the interest rate paid on funds invested (%).<br><strong>Reinvestiment Return Rate</strong> the return (as a percentage) earned on reinvestment of income received from the investment (%).</p><p>Ex: <strong>MIRR([-120000, 39000, 30000, 21000, 37000, 46000], 10, 12)</strong><br>Result: <strong>12.61</strong></p>"
							},

							{
								"value" : "NUMBERFORMAT",
								"code"  : "NUMBERFORMAT(",
								"tip"   : "<p><strong>Format a Number</strong></p>\
										  <p>One parameters: number</p>\
										  <p>Ex: <strong>NUMBERFORMAT(-2530023420269.123456)</strong><br /> \
										  Result: <strong>-2,530,023,420,269</strong></p>\
										  <p>Ex: <strong>NUMBERFORMAT(25000.123456, {precision:2})</strong><br /> \
										  Result: <strong>25,000.12</strong></p>\
										  <p><strong>Format Currency</strong></p>\
										  <p>Format a number to a certain currency. Two parameters: number, settings (optional). If settings option is a string it is treated as a currency name. If it is an object it is used as currency settings.</p>\
										  <p>Ex: <strong>NUMBERFORMAT(25000.123456, &quot;USD&quot;)</strong><br />\
										  Result: <strong>$25,000.12</strong></p>\
										  <p>Settings can be format, and then override with options.</p>\
										  <p>Ex: <strong>NUMBERFORMAT(-25000.123456, &quot;GBP&quot;, { negative: &quot;()&quot;, precision: 3, thousand: &quot;&quot; })</strong><br />\
										  Result: <strong>£(25000.123)</strong></p>\
										  <p><strong>Format a Percent</strong></p>\
										  <p>Format a number with a certain precision. Two parameters: number, settings (&quot;percent&quot; is a format)</p>\
										  <p>Ex: <strong>NUMBERFORMAT(25000.123456, &quot;percent&quot;)</strong><br /> \
										  Result: <strong>25,000%</strong></p>"
							},

							{
								"value" : "ADDFORMAT",
								"code"  : "ADDFORMAT(",
								"tip"   : "<p><strong>Create a Currency</strong></p>\
										  <p>You may create a currency. The library comes with &quot;USD&quot;, &quot;GBP&quot;, and &quot;EUR&quot; currency formats and &quot;number&quot; and &quot;percent&quot; numeric formats. Two parameters: key, settings</p>\
										  <p>Ex: <strong>ADDFORMAT(&quot;Dollars&quot;, { before: &quot;&quot;, after: &quot; Dollars&quot;, precision: 0, thousand: &quot;,&quot;, group: 3, decimal: &quot;.&quot;, negative: &quot;-&quot; })</strong><br /> \
										  Result: <strong>true</strong></p>\
										  <p>Ex: <strong>NUMBERFORMAT(25000.123456, &quot;Dollars&quot;)</strong><br /> \
										  Result: <strong>25,000 Dollars</strong></p>"
							},

							{
								"value" : "REMOVEFORMAT",
								"code"  : "REMOVEFORMAT(",
								"tip"   : "<p><strong>Remove a Currency</strong></p>\
										  <p>To remove a currency. One parameter: key</p>\
										  <p>Ex: <strong>REMOVEFORMAT(&quot;Dollars&quot;)</strong><br /> \
										  Result: <strong>true</strong></p>"
							}
						]
		}
	}
};