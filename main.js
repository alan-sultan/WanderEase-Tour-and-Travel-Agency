const menuBtn = document.getElementById("menu-btn");
const navLinks = document.getElementById("nav-links");
const menuBtnIcon = menuBtn.querySelector("i");

menuBtn.addEventListener("click", (e) => {
    navLinks.classList.toggle("open");

    const isOpen = navLinks.classList.contains("open");
    menuBtnIcon.setAttribute("class", isOpen ? "ri-close-line" : "ri-menu-line");
});

navLinks.addEventListener("click", (e) => {
    navLinks.classList.remove("open");
    menuBtnIcon.setAttribute("class", "ri-menu-line");
});

const scrollRevealOption = {
    distance: "50px",
    origin: "bottom",
    duration: 1000,
};

ScrollReveal().reveal(".header__container h1", {
    ...scrollRevealOption,
});
ScrollReveal().reveal(".header__container p", {
    ...scrollRevealOption,
    delay: 500,
});
ScrollReveal().reveal(".header__container form", {
    ...scrollRevealOption,
    delay: 1000,
});

ScrollReveal().reveal(".feature__card", {
    duration: 1000,
    interval: 500,
});

ScrollReveal().reveal(".destination__card", {
    ...scrollRevealOption,
    interval: 500,
});

ScrollReveal().reveal(".package__card", {
    ...scrollRevealOption,
    interval: 500,
});


const cards = [
    {
        photo: "images/client-1.jpg",
        name: "Alice Smith",
        date: "15 Sep, 2024",
        stars: "★★★★",
        review:
            "The support groups have been an incredible source of comfort for me. It is a safe space where I can openly share my challenges and hear others experiences. Knowing I am not alone has made a huge difference in my healing journey.",
    },
    {
        photo: "images/client-2.jpg",
        name: "Sarah Johnson",
        date: "01 Jan, 2022",
        stars: "★★★★★",
        review:
            "TravelDream made my vacation planning effortless and enjoyable. Their best price guarantee really delivered!",
    },
    {
        photo: "images/client-3.jpg",
        name: "David Martinez",
        date: "10 Feb, 2024",
        stars: "★★★★",
        review:
            "TravelDream offers an unbeatable combination of great prices and top customer service. Highly recommended!",
    },
    {
        photo: "images/client-4.jpg",
        name: "Olivia Brown",
        date: "09 Nov, 2024",
        stars: "★★★★★",
        review:
            "Booking with TravelDream was a breeze. Their user-friendly interface and instant confirmation saved me so much time.",
    },
    {
        photo: "images/client-5.jpg",
        name: "James Wilson",
        date: "09 Nov, 2024",
        stars: "★★★★★",
        review:
            "From booking to travel, every step was seamless. TravelDream truly makes travel dreams come true!"
    }
];

let currentIndex = 0;

function changeCard(direction) {
    if (direction === "right") {
        currentIndex = (currentIndex + 1) % cards.length;
    } else if (direction === "left") {
        currentIndex = (currentIndex - 1 + cards.length) % cards.length;
    }

    const card = cards[currentIndex];

    document.getElementById("photo").src = card.photo;
    document.getElementById("name").textContent = card.name;
    document.getElementById("date").textContent = card.date;
    document.getElementById("stars").textContent = card.stars;
    document.getElementById("writtenReview").textContent = card.review;
}
