// Configuration Constants
const CONFIG = {
    rates: {
        hotel: {
            'hotel-1': 300000,
            'hotel-2': 200000,
            'hotel-3': 225000,
            guestSurcharge: 0.2,
            maxGuests: 4
        },
        housing: {
            'housing-1-monthly': 1000000,
            'housing-1-quarterly': 2850000,
            'housing-1-yearly': 9600000,
            'housing-2-monthly': 800000,
            'housing-2-quarterly': 2280000,
            'housing-2-yearly': 7680000
        },
        venue: {
            base: 7500000,
            minHeadcount: 100,
            maxHeadcount: 500,
            headcountIncrement: 50,
            headcountPrice: 150000,
            addons: {
                floral: { price: 0, name: "Floral Arrangement" },
                angpao: { price: 50000, name: "Angpao Boxes" },
                cake: { price: 150000, name: "Decorative Cakes" },
                projector: { price: 150000, name: "LCD Projector" },
                dressing: { price: 0, name: "Dressing Room" },
                catering: {
                    base: 1500000,
                    increment: 100,
                    price: 950000,
                    name: "Catering"
                },
                sound: { price: 275000, name: "Sound System" },
                pianist: { price: 375000, name: "Pianist" }
            }
        }
    },
    dateFormatOptions: { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
    },
    monthsToShow: 2
};