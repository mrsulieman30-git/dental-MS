import { format, parseISO, differenceInYears } from 'date-fns';

export const formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

export const formatDate = (date) => {
    if (!date) return 'N/A';
    return format(parseISO(date.toString()), 'MMM dd, yyyy');
};

export const formatDateTime = (datetime) => {
    if (!datetime) return 'N/A';
    return format(parseISO(datetime.toString()), 'MMM dd, yyyy h:mm a');
};

export const formatPhone = (phone) => {
    if (!phone) return 'N/A';
    const cleaned = ('' + phone).replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        return '(' + match[1] + ') ' + match[2] + '-' + match[3];
    }
    return phone;
};

export const formatPatientNumber = (num) => {
    if (!num) return 'N/A';
    return `PT-${num.toString().padStart(6, '0')}`;
};

export const formatAge = (dob) => {
    if (!dob) return 'N/A';
    const birthDate = parseISO(dob.toString());
    return differenceInYears(new Date(), birthDate);
};

export const formatAddress = (address) => {
    if (!address) return 'N/A';
    if (typeof address === 'string') return address;
    const parts = [
        address.address_line1,
        address.address_line2,
        address.city,
        address.state,
        address.zip
    ].filter(Boolean);
    return parts.join(', ');
};
