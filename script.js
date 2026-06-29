const slides = document.querySelector(".slides");
const slide = document.querySelectorAll(".slide");

const nextBtn = document.getElementById("next");
const prevBtn = document.getElementById("prev");

const dots = document.querySelectorAll(".dot");

let index = 0;
const total = slide.length;

// Show Slide
function showSlide(i){

    slides.style.transform = `translateX(-${i * 100}%)`;
    slides.style.transition = "0.8s ease";

    dots.forEach(dot=>dot.classList.remove("active"));
    dots[i].classList.add("active");
}

// Next
function nextSlide(){

    index++;

    if(index >= total){
        index = 0;
    }

    showSlide(index);
}

// Previous
function prevSlide(){

    index--;

    if(index < 0){
        index = total-1;
    }

    showSlide(index);
}

nextBtn.addEventListener("click",()=>{

    nextSlide();
    resetTimer();

});

prevBtn.addEventListener("click",()=>{

    prevSlide();
    resetTimer();

});

// Dot Click

dots.forEach((dot,i)=>{

    dot.addEventListener("click",()=>{

        index=i;

        showSlide(index);

        resetTimer();

    });

});

// Auto Slide

let auto = setInterval(nextSlide,5000);

// Restart Timer

function resetTimer(){

    clearInterval(auto);

    auto = setInterval(nextSlide,5000);

}

// Start

showSlide(index);

