// Theme catalogue. Each row is one shipped theme.
// archetype + accent + treatment is all it takes; the generator does the rest.
// treatment: 'solid' | 'texture' | 'photo'. Photo themes need a `background`
// file in theme-toolkit/backgrounds/ and ship it self-hosted.
// icon: a Tabler glyph name (in theme-toolkit/icons/) tiled faintly behind the
// page so the theme reads as its profession. Photo themes skip the motif.
// category drives picker grouping (parsed from readme "Theme Category:").

module.exports = [
  // ===================== TRADE (dark, Oswald, sharp + accent bar) ==========
  { slug: 'plumber',      name: 'Plumber',      category: 'Trade', archetype: 'trade', accent: '#FF6B2C', treatment: 'solid', icon: 'tools',
    blurb: 'Bold, high-contrast dark theme for plumbers and contractors who want to look dependable.' },
  { slug: 'electrician',  name: 'Electrician',  category: 'Trade', archetype: 'trade', accent: '#2D9CFF', treatment: 'solid', icon: 'bolt',
    blurb: 'Electric-blue accents on a deep slate background — sharp and trustworthy.' },
  { slug: 'mechanic',     name: 'Mechanic',     category: 'Trade', archetype: 'trade', accent: '#FFC400', treatment: 'solid', icon: 'engine',
    blurb: 'Hi-vis yellow on dark — a garage-floor look for mechanics and auto shops.' },
  { slug: 'hvac',         name: 'HVAC Tech',    category: 'Trade', archetype: 'trade', accent: '#19B5C9', treatment: 'solid', icon: 'air-conditioning',
    blurb: 'Cool teal accents for heating, cooling and air specialists.' },
  { slug: 'contractor',   name: 'Contractor',   category: 'Trade', archetype: 'trade', accent: '#E5484D', treatment: 'solid', icon: 'hammer',
    blurb: 'Toolbox red on charcoal — solid and direct for general contractors.' },
  { slug: 'roofer',       name: 'Roofer',       category: 'Trade', archetype: 'trade', accent: '#C0431F', treatment: 'solid', icon: 'home-2',
    blurb: 'Rust-orange accents with a grounded, weatherproof feel.' },
  { slug: 'landscaper',   name: 'Landscaper',   category: 'Trade', archetype: 'trade', accent: '#36B37E', treatment: 'solid', icon: 'plant-2',
    blurb: 'Green accents over a subtle leaf motif — for landscaping and lawn care.' },
  { slug: 'handyman',     name: 'Handyman',     category: 'Trade', archetype: 'trade', accent: '#E58A2C', treatment: 'solid', icon: 'tool',
    blurb: 'Warm amber on dark — approachable and reliable for handyman services.' },
  { slug: 'mover',        name: 'Moving Co.',   category: 'Trade', archetype: 'trade', accent: '#5B8DEF', treatment: 'solid', icon: 'truck',
    blurb: 'Clean blue accents for movers and delivery crews.' },

  // ===================== BEAUTY & WELLNESS (Bloom unless noted) ============
  { slug: 'hairstylist',  name: 'Hairstylist',  category: 'Beauty', archetype: 'bloom', accent: '#E8A6A1', treatment: 'solid', icon: 'scissors',
    blurb: 'Soft, airy light theme with a subtle scissors motif — made for salons and stylists.' },
  { slug: 'esthetician',  name: 'Esthetician',  category: 'Beauty', archetype: 'bloom', accent: '#9CAF88', treatment: 'solid', icon: 'sparkles',
    blurb: 'Calm sage tones for skincare and facial specialists.' },
  { slug: 'nail-tech',    name: 'Nail Tech',    category: 'Beauty', archetype: 'bloom', accent: '#B6A6C9', treatment: 'solid', icon: 'nail-polish',
    blurb: 'Lavender accents on cream — polished and pretty for nail artists.' },
  { slug: 'makeup-artist',name: 'Makeup Artist',category: 'Beauty', archetype: 'bloom', accent: '#C97B5A', treatment: 'solid', icon: 'brush',
    blurb: 'Warm terracotta on soft cream — flattering for MUAs.' },
  { slug: 'lash-tech',    name: 'Lash Tech',    category: 'Beauty', archetype: 'bloom', accent: '#C99BB0', treatment: 'solid', icon: 'eyelash',
    blurb: 'Dusty rose accents — delicate and feminine for lash and brow studios.' },
  { slug: 'barber',       name: 'Barber',       category: 'Beauty', archetype: 'gallery', accent: '#E0A422', treatment: 'photo', background: 'barber.webp', scrim: 'dark',
    blurb: 'Dark, vintage barbershop interior with warm amber detailing.' },

  // ===================== WELLNESS ==========================================
  { slug: 'yoga-instructor', name: 'Yoga Instructor', category: 'Wellness', archetype: 'bloom', accent: '#7FB7BE', treatment: 'photo', background: 'yoga-instructor.webp', scrim: 'light',
    blurb: 'Serene soft-teal palette over a calm studio, for yoga and movement teachers.' },
  { slug: 'massage-therapist', name: 'Massage Therapist', category: 'Wellness', archetype: 'bloom', accent: '#A8B79A', treatment: 'solid', icon: 'massage',
    blurb: 'Muted, restful greens for massage and bodywork.' },
  { slug: 'spa',          name: 'Day Spa',      category: 'Wellness', archetype: 'bloom', accent: '#9CAF88', treatment: 'photo', background: 'spa.webp', scrim: 'light',
    blurb: 'A tranquil sage-and-cream theme over a minimal spa scene, for spas and wellness studios.' },
  { slug: 'personal-trainer', name: 'Personal Trainer', category: 'Wellness', archetype: 'trade', accent: '#36C26E', treatment: 'solid', icon: 'barbell',
    blurb: 'Bold, energetic dark theme with a fitness-green accent.' },

  // ===================== FOOD & DRINK (Hearth unless noted) ================
  { slug: 'bakery',       name: 'Bakery',       category: 'Food', archetype: 'hearth', accent: '#D2683E', treatment: 'photo', background: 'bakery.webp', scrim: 'light',
    blurb: 'Warm, appetizing theme with a photographic backdrop for bakeries and cafés.' },
  { slug: 'restaurant',   name: 'Restaurant',   category: 'Food', archetype: 'hearth', accent: '#D14B3D', treatment: 'photo', background: 'restaurant.webp', scrim: 'light',
    blurb: 'Inviting tomato-red over a warm dining-room interior, for restaurants and diners.' },
  { slug: 'coffee-shop',  name: 'Coffee Shop',  category: 'Food', archetype: 'hearth', accent: '#8A5A2B', treatment: 'photo', background: 'coffee-shop.webp', scrim: 'light',
    blurb: 'Roasty coffee-brown tones over a warm café interior, for cafés and roasters.' },
  { slug: 'food-truck',   name: 'Food Truck',   category: 'Food', archetype: 'hearth', accent: '#E0A422', treatment: 'solid', icon: 'tools-kitchen-2',
    blurb: 'Bright mustard accents — fun and casual for food trucks.' },
  { slug: 'caterer',      name: 'Caterer',      category: 'Food', archetype: 'hearth', accent: '#6F7B4B', treatment: 'solid', icon: 'soup',
    blurb: 'Earthy olive tones for caterers and private chefs.' },
  { slug: 'pizzeria',     name: 'Pizzeria',     category: 'Food', archetype: 'hearth', accent: '#C0392B', treatment: 'solid', icon: 'pizza',
    blurb: 'Classic deep red on warm cream for pizzerias.' },
  { slug: 'brewery',      name: 'Brewery',      category: 'Food', archetype: 'hearth', accent: '#C9852B', treatment: 'solid', icon: 'beer',
    blurb: 'Amber-malt accents for breweries and taprooms.' },
  { slug: 'bartender',    name: 'Bartender',    category: 'Food', archetype: 'gallery', accent: '#E0A422', treatment: 'solid', icon: 'beer',
    blurb: 'Moody, dark cocktail-bar theme with warm amber lighting.' },

  // ===================== CREATIVE (Gallery unless noted) ===================
  { slug: 'tattoo-artist',name: 'Tattoo Artist',category: 'Creative', archetype: 'gallery', accent: '#FF2D78', treatment: 'photo', background: 'tattoo.webp', scrim: 'dark',
    blurb: 'Vivid dark gallery theme with a studio backdrop for tattoo artists and creatives.' },
  { slug: 'photographer', name: 'Photographer', category: 'Creative', archetype: 'gallery', accent: '#FFB020', treatment: 'photo', background: 'photographer.webp', scrim: 'dark',
    blurb: 'Dark gallery theme over a studio backdrop that lets the work shine — for photographers.' },
  { slug: 'musician',     name: 'Musician',     category: 'Creative', archetype: 'gallery', accent: '#8B5CF6', treatment: 'photo', background: 'musician.webp', scrim: 'dark',
    blurb: 'Electric violet over moody stage lighting — for musicians and bands.' },
  { slug: 'dj',           name: 'DJ',           category: 'Creative', archetype: 'gallery', accent: '#00E0D0', treatment: 'solid', icon: 'headphones',
    blurb: 'Neon-cyan accents on dark — high-energy for DJs and producers.' },
  { slug: 'artist',       name: 'Artist',       category: 'Creative', archetype: 'gallery', accent: '#FF7849', treatment: 'solid', icon: 'palette',
    blurb: 'Warm coral pop on a dark canvas — for visual artists.' },
  { slug: 'designer',     name: 'Designer',     category: 'Creative', archetype: 'gallery', accent: '#3DDC84', treatment: 'solid', icon: 'pencil',
    blurb: 'Crisp green accents on dark — modern and technical for designers.' },
  { slug: 'writer',       name: 'Writer',       category: 'Creative', archetype: 'studio', accent: '#6B4E3D', treatment: 'solid', icon: 'feather',
    blurb: 'Editorial serif theme in sepia ink — for writers and authors.' },

  // ===================== PROFESSIONAL (Studio) =============================
  { slug: 'lawyer',       name: 'Lawyer',       category: 'Professional', archetype: 'studio', accent: '#7B2D3A', treatment: 'solid', icon: 'gavel',
    blurb: 'Refined editorial theme that reads credible and established for legal and advisory work.' },
  { slug: 'realtor',      name: 'Realtor',      category: 'Professional', archetype: 'studio', accent: '#1F3A5F', treatment: 'solid', icon: 'home',
    blurb: 'Navy-and-ivory theme that signals trust for real estate.' },
  { slug: 'accountant',   name: 'Accountant',   category: 'Professional', archetype: 'studio', accent: '#2F5D50', treatment: 'solid', icon: 'calculator',
    blurb: 'Forest-green accents — composed and precise for accountants.' },
  { slug: 'consultant',   name: 'Consultant',   category: 'Professional', archetype: 'studio', accent: '#3A3A3A', treatment: 'solid', icon: 'bulb',
    blurb: 'Minimal ink-on-ivory theme for consultants and advisors.' },
  { slug: 'financial-advisor', name: 'Financial Advisor', category: 'Professional', archetype: 'studio', accent: '#243B53', treatment: 'solid', icon: 'chart-line',
    blurb: 'Deep-navy editorial theme for finance and wealth professionals.' },
  { slug: 'therapist',    name: 'Therapist',    category: 'Professional', archetype: 'studio', accent: '#4E6E5D', treatment: 'solid', icon: 'armchair',
    blurb: 'Soft forest tones — calm and reassuring for therapists and coaches.' },
  { slug: 'architect',    name: 'Architect',    category: 'Professional', archetype: 'studio', accent: '#3D4A52', treatment: 'solid', icon: 'ruler-2',
    blurb: 'Slate-grey editorial theme for architects and studios.' },

  // ===================== LIFESTYLE / SPECIAL INTEREST =====================
  { slug: 'fitness-influencer', name: 'Fitness Creator', category: 'Lifestyle', archetype: 'gallery', accent: '#FF4D6D', treatment: 'solid', icon: 'run',
    blurb: 'High-energy dark theme with a punchy coral-red for fitness creators.' },
  { slug: 'travel-creator', name: 'Travel Creator', category: 'Lifestyle', archetype: 'gallery', accent: '#00C2D0', treatment: 'photo', background: 'travel-creator.webp', scrim: 'dark',
    blurb: 'Cyan-on-dark wanderlust theme over a dramatic landscape, for travel creators.' },
  { slug: 'lifestyle-blogger', name: 'Lifestyle Blogger', category: 'Lifestyle', archetype: 'bloom', accent: '#C97B5A', treatment: 'solid', icon: 'coffee',
    blurb: 'Soft, warm light theme for lifestyle and everyday bloggers.' },
  { slug: 'florist',      name: 'Florist',      category: 'Lifestyle', archetype: 'bloom', accent: '#D98AA6', treatment: 'photo', background: 'florist.webp', scrim: 'light',
    blurb: 'Blush-pink botanical theme over soft florals, for florists and flower studios.' },
  { slug: 'wedding-planner', name: 'Wedding Planner', category: 'Lifestyle', archetype: 'studio', accent: '#9A7B5A', treatment: 'photo', background: 'wedding-planner.webp', scrim: 'light',
    blurb: 'Elegant gold-and-ivory editorial theme over refined florals, for wedding and event planners.' },
  { slug: 'pet-groomer',  name: 'Pet Groomer',  category: 'Lifestyle', archetype: 'bloom', accent: '#7FB7BE', treatment: 'solid', icon: 'paw',
    blurb: 'Friendly soft-teal theme for pet groomers, sitters and vets.' },
];
