// Mobile Menu
const openMenu = document.getElementById("openMenu");
const closeMenu = document.getElementById("closeMenu");
const mobileMenu = document.getElementById("mobileMenu");
const navbar = document.getElementById("navbar");

openMenu.addEventListener("click", () => {
    mobileMenu.classList.remove("-translate-x-full");
    navbar.classList.remove("backdrop-blur");
    document.body.classList.add("max-md:overflow-hidden");
});

closeMenu.addEventListener("click", () => {
    mobileMenu.classList.add("-translate-x-full");
    navbar.classList.add("backdrop-blur");
    document.body.classList.remove("max-md:overflow-hidden");
});

// Logo Section
const logos = ["framer", "huawei", "instagram", "microsoft", "walmart"];
const track = document.getElementById("logo-track");

track.innerHTML = [...logos, ...logos].map((name) => `<img class="mx-11" src="/assets/companies-logo/${name}.svg" alt="${name.charAt(0).toUpperCase() + name.slice(1)}" width="100" height="100" draggable="false"/>`).join("");

// Testimonials Section
const cardsData = [
    {
        image: 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?q=80&w=200',
        name: 'Briar Martin',
        handle: '@neilstellar',
        date: 'April 20, 2025'
    },
    {
        image: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=200',
        name: 'Avery Johnson',
        handle: '@averywrites',
        date: 'May 10, 2025'
    },
    {
        image: 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=200&auto=format&fit=crop&q=60',
        name: 'Jordan Lee',
        handle: '@jordantalks',
        date: 'June 5, 2025'
    },
    {
        image: 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=200&auto=format&fit=crop&q=60',
        name: 'Avery Johnson',
        handle: '@averywrites',
        date: 'May 10, 2025'
    },
];

const row1 = document.getElementById('row1');
const row2 = document.getElementById('row2');

const createCard = (card) => `
    <div class="p-4 rounded-lg mx-4 shadow hover:shadow-lg transition-all duration-200 w-72 shrink-0">
        <div class="flex gap-2">
            <img class="size-11 rounded-full" src="${card.image}" alt="User Image">
            <div class="flex flex-col">
                <div class="flex items-center gap-1">
                    <p>${card.name}</p>
                    <svg class="mt-0.5" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.555.72a4 4 0 0 1-.297.24c-.179.12-.38.202-.59.244a4 4 0 0 1-.38.041c-.48.039-.721.058-.922.129a1.63 1.63 0 0 0-.992.992c-.071.2-.09.441-.129.922a4 4 0 0 1-.041.38 1.6 1.6 0 0 1-.245.59 3 3 0 0 1-.239.297c-.313.368-.47.551-.56.743-.213.444-.213.96 0 1.404.09.192.247.375.56.743.125.146.187.219.24.297.12.179.202.38.244.59.018.093.026.189.041.38.039.48.058.721.129.922.163.464.528.829.992.992.2.071.441.09.922.129.191.015.287.023.38.041.21.042.411.125.59.245.078.052.151.114.297.239.368.313.551.47.743.56.444.213.96.213 1.404 0 .192-.09.375-.247.743-.56.146-.125.219-.187.297-.24.179-.12.38-.202.59-.244a4 4 0 0 1 .38-.041c.48-.039.721-.058.922-.129.464-.163.829-.528.992-.992.071-.2.09-.441.129-.922a4 4 0 0 1 .041-.38c.042-.21.125-.411.245-.59.052-.078.114-.151.239-.297.313-.368.47-.551.56-.743.213-.444.213-.96 0-1.404-.09-.192-.247-.375-.56-.743a4 4 0 0 1-.24-.297 1.6 1.6 0 0 1-.244-.59 3 3 0 0 1-.041-.38c-.039-.48-.058-.721-.129-.922a1.63 1.63 0 0 0-.992-.992c-.2-.071-.441-.09-.922-.129a4 4 0 0 1-.38-.041 1.6 1.6 0 0 1-.59-.245A3 3 0 0 1 7.445.72C7.077.407 6.894.25 6.702.16a1.63 1.63 0 0 0-1.404 0c-.192.09-.375.247-.743.56m4.07 3.998a.488.488 0 0 0-.691-.69l-2.91 2.91-.958-.957a.488.488 0 0 0-.69.69l1.302 1.302c.19.191.5.191.69 0z" fill="#2196F3" />
                    </svg>
                </div>
                <span class="text-xs text-slate-500">${card.handle}</span>
            </div>
        </div>
        <p class="text-sm py-4 text-gray-800">Radiant made undercutting all of our competitors an absolute breeze.</p>
    </div>
`;

const renderCards = (target) => {
    const doubled = [...cardsData, ...cardsData];
    doubled.forEach(card => target.insertAdjacentHTML('beforeend', createCard(card)));
};

renderCards(row1);
renderCards(row2);

// Pricing Section
const pricingData = [
    {
        title: "Basic Plan",
        price: 29,
        features: [
            "5 Projects",
            "10 GB Storage",
            "Basic Support",
            "Community Access",
            "Basic code review",
        ],
        buttonText: "Get Started",
    },
    {
        title: "Pro Plan",
        price: 79,
        mostPopular: true,
        features: [
            "50 Projects",
            "100 GB Storage",
            "Priority Support",
            "Team Collaboration",
            "Advanced Analytics",
            "Premium Code Review",
        ],
        buttonText: "Upgrade Now",
    },
    {
        title: "Enterprise Plan",
        price: 149,
        features: [
            "Unlimited Projects",
            "1 TB Storage",
            "24/7 Dedicated Support",
            "Custom Integrations",
            "SLA Guarantee",
        ],
        buttonText: "Contact Sales",
    },
];

const pricingContainer = document.getElementById("pricing");

pricingContainer.innerHTML = pricingData.map(plan => `
    <div class="p-6 rounded-2xl max-w-75 w-full shadow-[0px_4px_26px] shadow-black/6 ${plan.mostPopular ? "relative pt-12 bg-gradient-to-b from-indigo-600 to-violet-600 text-white" : "bg-white/50"}">

    ${plan.mostPopular ? `<div class="flex items-center text-xs gap-1 py-1.5 px-2 text-indigo-600 absolute top-4 right-4 rounded bg-white font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles-icon lucide-sparkles"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"/><path d="M20 2v4"/><path d="M22 4h-4"/><circle cx="4" cy="20" r="2"/></svg>
        <p>Most Popular</p>
    </div>` : ""}

    <p class="font-medium ${plan.mostPopular ? "text-white" : ""}">${plan.title}</p>
    <h4 class="text-3xl font-semibold mt-1 ${plan.mostPopular ? "text-white" : ""}">
        $${plan.price}<span class="font-normal text-sm ${plan.mostPopular ? "text-white" : "text-slate-300"}">/mo</span>
    </h4>

    <hr class="my-8 ${plan.mostPopular ? "border-gray-300" : "border-slate-300"}" />

    <div class="space-y-2 ${plan.mostPopular ? "text-white" : "text-slate-600"}">
        ${plan.features.map(f => `
        <div class="flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="${plan.mostPopular ? "text-white" : "text-indigo-600"}"><path d="M20 6 9 17l-5-5"/></svg>
            <span>${f}</span>
        </div>
        `).join("")}
    </div>

    <button class="transition w-full py-3 rounded-lg font-medium mt-8 
        ${plan.mostPopular
    ? "bg-white hover:bg-slate-100 text-slate-800"
    : "bg-indigo-600 hover:bg-indigo-700 text-white"}">
        ${plan.buttonText}
    </button>
    </div>
`).join("");

// FAQ Data
const faqsData = [
    {
        question: "What is this SaaS app used for?",
        answer: "This app helps businesses manage their workflows more efficiently by automating tasks, tracking performance, and integrating with third-party tools."
    },
    {
        question: "Is there a free trial available?",
        answer: "Yes, we offer a 14-day free trial with full access to all features. No credit card is required to start the trial."
    },
    {
        question: "Can I change my subscription plan later?",
        answer: "Absolutely! You can upgrade or downgrade your plan at any time from your account settings."
    },
    {
        question: "How is my data secured?",
        answer: "We use industry-standard encryption, regular security audits, and secure data centers to ensure your data is safe and protected."
    },
    {
        question: "Do you offer customer support?",
        answer: "Yes, our support team is available 24/7 via live chat and email. We also have a help center with detailed documentation and tutorials."
    }
];

const faqContainer = document.getElementById("faq-container");

faqContainer.innerHTML = faqsData.map((faq, index) => `
      <div class="border-b border-slate-300 py-4 cursor-pointer w-full" data-index="${index}">
        <div class="flex items-center justify-between">
          <h3 class="text-base font-medium">${faq.question}</h3>
          <!-- ChevronDown icon placeholder -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition-transform duration-500 ease-in-out" data-chevron><path d="m6 9 6 6 6-6"/></svg>
        </div>
        <p class="faq-answer text-sm text-slate-600 transition-all duration-500 ease-in-out max-w-xl opacity-0 max-h-0 overflow-hidden -translate-y-2">
          ${faq.answer}
        </p>
      </div>
    `).join("");

// Accordion Logic
const faqItems = document.querySelectorAll("#faq-container > div");

faqItems.forEach(item => {
    const chevron = item.querySelector("[data-chevron]");
    const answer = item.querySelector(".faq-answer");

    item.addEventListener("click", () => {
        const isOpen = answer.classList.contains("opacity-100");

        // Close all
        faqItems.forEach(i => {
            i.querySelector(".faq-answer").classList.remove("opacity-100", "max-h-[500px]", "translate-y-0", "pt-4");
            i.querySelector(".faq-answer").classList.add("opacity-0", "max-h-0", "-translate-y-2");
            i.querySelector("[data-chevron]").classList.remove("rotate-180");
        });

        // Toggle current
        if (!isOpen) {
            answer.classList.add("opacity-100", "max-h-[500px]", "translate-y-0", "pt-4");
            answer.classList.remove("opacity-0", "max-h-0", "-translate-y-2");
            chevron.classList.add("rotate-180");
        }
    });
});