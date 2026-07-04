import { initVisor } from "./visor";
import AOS from "aos";
import "aos/dist/aos.css";

import { initVideoVisor } from "./visorVideo";
window.initVideoVisor = initVideoVisor;

AOS.init({
    duration: 800, 
    once: true, 
});

window.initVisor = initVisor;
