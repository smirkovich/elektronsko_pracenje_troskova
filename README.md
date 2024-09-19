Opis projekta:

Cilj projekta je da se kreira web aplikacija-sajt za praćenje mesečnih troškova u okviru jednog
domaćinstva. Aplikacija razlikuje četiri nivoa pristupa: gost, registrovan/prijavljen korisnik-
član domaćinstva, registrovan/prijavljen korisnik-administrator domaćinstva i administrator
sistema.
Gost
• može da pogleda ponuđene informacije na sajtu
• može da izvrši registraciju na sajtu
Registrovan/prijavljen korisnik-administrator domaćinstva
• može da kreira domaćinstvo
• može da šalje poziv članovima domaćinstva da se registruju
• može da promeni svoje profilne podatke (lozinka, ime, prezime, broj telefona, adresu,
naziv domaćinstva)
• može da unosi troškove
• može da pregleda troškove svog domaćinstva
• može da kreira, menja i briše kategorije troškova (na primer: struja, hrana, obuća...)
• može da zahteva promenu lozinke (zaboravljena lozinka)
Registrovan/prijavljen korisnik-član domaćinstva
• može da unosi troškove
• može da pregleda troškove svog domaćinstva
• može da zahteva promenu lozinke (zaboravljena lozinka)
Administrator
• može da pregleda sve korisnike i domaćinstva
• može da pregleda statistiku logovanja korisnika na sistem
• nema uvid u zapise o troškovima domaćinstva
Registraciju korisnika mora biti urađena na bezbedan način i obaveznim slanjem aktivacionog
linka putem e-maila. Slanje linka koristiti i kod zahteva za promenu lozinke. Ne sme se
dozvoliti registracija više korisničkih naloga sa istom e-mail adresom. E-mail adresa mora
biti jedinstvena i ona predstavlja korisničko ime.
Prijavljeni korisnik kreira domaćinstvo unošenjem imena domaćinstva. Nakon toga ima
mogućnost da šalje putem e-maila poziv za registraciju ostalim članovima domaćinstva. U e-
mailu je potrebno da se nalazi podatak koji će biti jednoznačan za domaćinstvo (na primer id
domaćinstva) i da se na osnovu njega zna za koje domaćinstvo se registruje korisnik.
Prijavljeni korisnici odabirom kategorije troškova mogu dodati trošak. Svaki unos treba da
sadrži naziv, količinu troška, opis troška, datum unosa i informaciju o članu domaćinstva koji
je unos izvršio. Datum treba da se automatski unosi.
2
Članovi domaćinstva treba da imaju jasno prikazanu informaciju o ukupnim troškovima kao i
o iznosu troškova za aktuelni mesec. Pored toga potrebno je izdvojiti tri kategorije sa najviše
ukupnih troškova.
Odabirom kategorija i/ili vremenskog opsega članovi domaćinstva mogu pogledati sve troškove
i informacija o njima. Potrebno je omogućiti sortiranje prikaza troškova po nazivu kategorije i
po najvećoj odnosno najmanjoj količini troškova.
Administratorski deo zaštiti upotrebom sesija (PHP). Sve korisničke lozinke „hešovati“ bcrypt
algoritmom.
Kreirati bazu podataka pod imenom cost i unutar nje tabele koje će zadovoljiti sve
funkcionalnosti projekta.
