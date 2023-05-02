/*
* finance.js v0.2
*
* Based On
*
* finance.js v0.1 by: Trent Richardson [http://trentrichardson.com]
* Copyright 2012 Trent Richardson
*
* The functions implemented by Ismael Ghalimi [https://gist.github.com/ghalimi]
* Copyright (c) 2012 Sutoiku, Inc. (MIT License)
*
* EGM Mathematical Finance class by Enrique Garcia M. <egarcia@egm.co>
*
* CALCULATED FIELD PROGRAMMERS
*  We've modified the object name and methods to avoid collisions with other libraries
*/

;(function(root){

	var lib = {};

	lib.cf_finance_version = '0.2';

	var daysDiff = function(d1, d2) {
			var oneDay = 24*60*60*1000;
			d1 = new Date( d1 );
			d2 = new Date( d2 );
			return Math.round( Math.abs((d1.getTime() - d2.getTime())/oneDay));
		};


	/*
	*	Defaults
	*/
	lib.settings = {
			format: 'number',
			formats: {
					USD: { before: '$', after: '', precision: 2, decimal: '.', thousand: ',', group: 3, negative: '-' }, // $
					GBP: { before:'£', after: '', precision: 2, decimal: '.', thousand: ',', group: 3, negative: '-' }, // £ or &#163;
					EUR: { before:'€', after: '', precision: 2, decimal: '.', thousand: ',', group: 3, negative: '-' }, // € or &#8364;
					percent: { before: '', after: '%', precision: 0, decimal: '.', thousand: ',', group: 3, negative: '-' },
					number: { before: '', after: '', precision: null, decimal: '.', thousand: ',', group: 3, negative: '-'},
					defaults: { before: '', after: '', precision: 0, decimal: '.', thousand: ',', group: 3, negative: '-' }
				}
		};

	lib.defaults = function(object, defs) {
			var key;
			object = object || {};
			defs = defs || {};

			for (key in defs) {
				if (defs.hasOwnProperty(key)) {
					if (object[key] == null) object[key] = defs[key];
				}
			}
			return object;
		};


	/*
	*	Formatting
	*/

	// add a currency format to library
	lib.ADDFORMAT = function(key, options){
			this.settings.formats[key] = this.defaults(options, this.settings.formats.defaults);
			return true;
		};

	// remove a currency format from library
	lib.REMOVEFORMAT = function(key){
			delete this.settings.formats[key];
			return true;
		};

	// format a number or currency
	lib.NUMBERFORMAT = function(num, settings, override){
			num = parseFloat(num);

			if(settings === undefined)
				settings = this.settings.formats[this.settings.format];
			else if(typeof settings == 'string')
				settings = this.settings.formats[settings];
			else settings = settings;
			settings = this.defaults(settings, this.settings.formats.defaults);

			if(override !== undefined)
				settings = this.defaults(override, settings);

			// set precision
			var tmp = num;
			if(settings.precision != null)
			{
				tmp = Math.abs(num);
				tmp = tmp.toFixed(settings.precision);
				num = num.toFixed(settings.precision);

			}

			var isNeg = num < 0,
				numParts = tmp.toString().split('.'),
				baseLen = numParts[0].length;

			// add thousands and group
			numParts[0] = numParts[0].replace(/(\d)/g, function(str, m1, offset, s){
					return (offset > 0 && (baseLen-offset) % settings.group == 0)? settings.thousand + m1 : m1;
				});

			// add decimal
			num = numParts.join(settings.decimal);

			// add negative if applicable
			if(isNeg && settings.negative){
				num = settings.negative[0] + num;
				if(settings.negative.length > 1)
					num += settings.negative[1];
			}

			return  settings.before + num + settings.after;
		};



	/*
	*	Financing
	*/

	// present value, calculate the present value of investment
	lib.PRESENTVALUE = lib.PV = function( rate, nper, pmt, fv, type ){
			var type = (typeof type === 'undefined') ? 0 : type,
				fv = (typeof fv === 'undefined') ? 0 : fv;

			if (rate === 0) return Math.abs(- pmt * nper - fv);
			return Math.abs((((1 - Math.pow(1 + rate, nper)) / rate) * pmt * (1 +rate * type) - fv) / Math.pow(1 + rate, nper));
  	};

	// future value, calculate the future value of an investment
	// based on an interest rate and a constant payment schedule
	lib.FUTUREVALUE = lib.FV = function( rate, nper, pmt, pv, type ){
			if( typeof pv == 'undefined' ) pv = 0;
			if( typeof type == 'undefined' ) type = 0;

			rate = rate/100;

			var pow = Math.pow(1 + rate, nper);
			var fv = 0;

			if (rate) {
				fv = (pmt * (1 + rate * type) * (1 - pow) / rate) - pv * pow;
			} else {
				fv = -1 * (pv + pmt * nper);
			}

			return fv;
		};

	//	calculate total of principle + interest (yearly) for x months
	lib.CALCULATEACCRUEDINTEREST = function(principle, months, rate){
			var i = rate/1200;
			return (principle * Math.pow(1+i,months)) - principle;
		};

	//	determine the amount financed
	lib.CALCULATEAMOUNT = function(finMonths, finInterest, finPayment){
			var result = 0;

			if(finInterest == 0){
				result = finPayment * finMonths;
			}
			else{
				var i = ((finInterest/100) / 12),
					i_to_m = Math.pow((i + 1), finMonths);
				result = finPayment / ((i * i_to_m) / (i_to_m - 1));
			}

			return result;
		};

	//	determine the months financed
	lib.CALCULATEMONTHS = function(finAmount, finInterest, finPayment){
			var result = 0;

			if(finInterest == 0){
				result = Math.ceil(finAmount / finPayment);
			}
			else{
				result = Math.round(( (-1/12) * (Math.log(1-(finAmount/finPayment)*((finInterest/100)/12))) / Math.log(1+((finInterest/100)/12)) )*12);
			}

			return result;
		};

	lib.NPER = function(rate, payment, present, future, type) {
			rate /=100;
			var type = type || 0,
				future = future || 0,
				num = payment * (1 + rate * type) - future * rate,
				den = (present * rate + payment * (1 + rate * type));
			return Math.log(num / den) / Math.log(1 + rate);
		};

	//	determine the interest rate financed http://www.hughchou.org/calc/formula.html
	lib.CALCULATEINTEREST = function(finAmount, finMonths, finPayment){
			var result = 0;

			var min_rate = 0, max_rate = 100;
			while(min_rate < max_rate-0.0001){
				var mid_rate = (min_rate + max_rate)/2,
					j = mid_rate / 1200,
					guessed_pmt = finAmount * ( j / (1-Math.pow(1+j, finMonths*-1)));

				if(guessed_pmt > finPayment){
					max_rate = mid_rate;
				}
				else{
					min_rate = mid_rate;
				}
			}
			return mid_rate;
		};

	//	determine the payment
	lib.CALCULATEPAYMENT = function(finAmount, finMonths, finInterest){
			var result = 0;
			if(finInterest == 0){
				result = finAmount / finMonths;
			}
			else{
				var i = ((finInterest/100) / 12),
					i_to_m = Math.pow((i + 1), finMonths);
				result = finAmount * ((i * i_to_m) / (i_to_m - 1));
			}

			return result;
		};

	// get an amortization schedule [ { principle: 0, interest: 0, payment: 0, paymentToPrinciple: 0, paymentToInterest: 0}, {}, {}...]
	lib.CALCULATEAMORTIZATION = function(finAmount, finMonths, finInterest, finDate){
			var payment = this.CALCULATEPAYMENT(finAmount, finMonths, finInterest),
				balance = finAmount,
				interest = 0.0,
				totalInterest = 0.0,
				schedule = [],
				currInterest = null,
				currPrinciple = null,
				currDate = (finDate !== undefined && finDate.constructor === Date)? finDate : (new Date());

			for(var i=0; i<finMonths; i++){
				currInterest = balance * finInterest/1200;
				totalInterest += currInterest;
				currPrinciple = payment - currInterest;
				balance -= currPrinciple;

				schedule.push({
						principle: balance,
						interest: totalInterest,
						payment: payment,
						paymentToPrinciple: currPrinciple,
						paymentToInterest: currInterest,
						date: new Date(currDate.getTime())
					});

				currDate.setMonth(currDate.getMonth()+1);
			}
			return schedule;
		};

	// The periodic payment for an annuity with constant interest rates
	lib.PMT = function(rate, nper, pv, fv, type) {
			if (!fv) fv = 0;
			if (!type) type = 0;
			rate /= 100;
			var result;
			if (rate === 0) {
				result = (pv + fv) / nper;
			} else {
				var term = Math.pow(1 + rate, nper);
				if (type === 1) {
					result = (fv * rate / (term - 1) + pv * rate / (1 - 1 / term)) / (1 + rate);
				} else {
					result = fv * rate / (term - 1) + pv * rate / (1 - 1 / term);
				}
			}
			return -result;
		};

	// The present value interest factor
	lib.PVIF = function(rate, nper) {
			return 1/Math.pow(1 + rate/100, nper);
		};

	// Calculate the future value interest factor of annuity
	lib.FVIFA = function(rate, nper) {
			rate = rate/100;
			return rate == 0 ? nper : (Math.pow(1+rate, nper) - 1) / rate;
		};

	// Interest payment for a given period for an investment based on periodic,
	// constant payments and a constant interest rate
	lib.IPMT = function(rate, per, nper, pv, fv, type) {
			if (!fv) fv = 0;
			if (!type) type = 0;

			// Compute payment
			var payment = this.PMT(rate, nper, pv, fv, type);

			// Compute interest
			var interest;
			if (per === 1) {
				if (type === 1) {
					interest = 0;
				} else {
					interest = -pv;
				}
			} else {
				if (type === 1) {
					interest = this.FUTUREVALUE(rate, per - 2, payment, pv, 1) - payment;
				} else {
					interest = this.FUTUREVALUE(rate, per - 1, payment, pv, 0);
				}
			}

			// Return interest
			return interest * rate/100;
		};

	// Returns for a given period the payment on the principal for an investment that is based
	// on periodic and constant payments and a constant interest rate.
	lib.PPMT = function(rate, per, nper, pv, fv, type) {
			if (!fv) fv = 0;
			if (!type) type = 0;
			if (per < 1 || (per >= nper + 1)) return null;

			var pmt = this.PMT(rate, nper, pv, fv, type);
			var ipmt = this.IPMT(rate, per, nper, pv, fv, type);
			return pmt - ipmt;
		};

	// Returns the net present value for a periodic schedule of cash flows
	lib.NPV = function(rate, vs) {
			var npv = 0;
			for (var i in vs ) npv += vs[i] / Math.pow(1+rate/100, i*1+1);
			return npv;
		};

	// Returns the net present value for a schedule of cash flows that is not necessarily periodic
	lib.XNPV = function(rate, vs, ds) {
			var xnpv = 0, fd = ds[0];
			for (var i in vs) xnpv += vs[i] / Math.pow(1 + rate/100, daysDiff(fd, ds[i])/365);
			return xnpv;
		};

	// Calculates the internal rate of return for a list of payments / Based on the XIRR operation.
	lib.IRR = function(vs, guess){
			var ds = [], d = new Date();
			for(var i = 0, h = vs.length; i < h; i++){ ds.push(GETDATETIMESTRING(d, 'yyyy-mm-dd')); d.setDate(d.getDate()+365); };
			return lib.XIRR(vs, ds, guess);
		};
	// Calculates the internal rate of return for a list of payments which take place on different dates.
	lib.XIRR = function (vs, ds, guess) {

			var fd = ds[ 0 ],
				irrResult = function(r) {
					r++;
					var rs = vs[0];
					for (var i = 1; i < vs.length; i++) {
						rs += vs[i] / Math.pow(r, daysDiff(ds[i], fd) / 365);
					}
					return rs;
				},
				irrResultDeriv = function(r) {
					r++;
					var rs = 0;
					for (var i = 1; i < vs.length; i++) {
						var frac = daysDiff(ds[i],fd) / 365;
						rs -= frac * vs[i] / Math.pow(r, frac + 1);
					}
					return rs;
				},
				p = false,
				n = false,
				resultRate = (typeof guess === 'undefined') ? 0.1 : guess/100,
				epsMax = 1e-10,
				iterMax = 50,
				newRate, epsRate, resultValue,
				iteration = 0,
				contLoop = true;

			for (var i = 0; i < vs.length; i++) {
				if (vs[i] > 0) p = true;
				if (vs[i] < 0) n = true;
			}

			if (!p || !n) return NaN;

			do {
				resultValue = irrResult(resultRate);
				newRate = resultRate - resultValue / irrResultDeriv(resultRate);
				epsRate = Math.abs(newRate - resultRate);
				resultRate = newRate;
				contLoop = (epsRate > epsMax) && (Math.abs(resultValue) > epsMax);
			} while(contLoop && (++iteration < iterMax));

			if(contLoop) return NaN;

			// Return internal rate of return
			return resultRate;
		};

	// Returns the modified internal rate of return for a series of periodic cash flows.
	lib.MIRR = function (v, fr, rr) {
			var n = v.length,
				p = [],
				i = [],
				num, den;

			for (var j = 0; j < n; j++)
			{
				if (v[j] < 0) p.push(v[j]);
				else i.push(v[j]);
			}

			num = -1*this.NPV(rr, i) * Math.pow(1 + rr/100, n - 1);
			den = this.NPV(fr, p) * (1 + fr/100);
			return (Math.pow(num / den, 1 / (n - 1)) - 1)*100;
		};

	root.CF_FINANCE = lib;

})(this);
