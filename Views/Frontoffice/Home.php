<?php
require_once __DIR__ . '/../../Controllers/UserController.php';
$role = $_SESSION['user_role'] ?? null;
$isLoggedIn = isset($_SESSION['user_id']);
$showWelcomeAssistant = $isLoggedIn && !empty($_SESSION['show_welcome_assistant']);
$welcomeName = trim($_SESSION['user_prenom'] ?? $_SESSION['user_nom'] ?? 'there');
if ($showWelcomeAssistant) {
    unset($_SESSION['show_welcome_assistant']);
}
$heroVideoSrc = '/Views/assets/img/hero-showcase.mp4';
$heroVideoPoster = '/Views/assets/img/logo1.png';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - SkillBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/Views/assets/css/skillbridge-front.css">
    <style>
        .jarvis-welcome-overlay {
            position: fixed;
            inset: 0;
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: #2f2925;
            background:
                radial-gradient(circle at 50% 12%, rgba(224,112,32,.16), transparent 30%),
                rgba(35, 25, 18, .28);
            backdrop-filter: blur(10px);
        }

        .jarvis-welcome-overlay.is-visible {
            display: flex;
        }

        .jarvis-panel {
            position: relative;
            width: min(620px, 100%);
            overflow: visible;
            border-radius: 28px;
            padding: 1.35rem;
            background:
                linear-gradient(145deg, rgba(255,255,255,.98), rgba(255,250,244,.96));
            border: 1px solid rgba(224,112,32,.18);
            box-shadow:
                0 28px 70px rgba(54,38,26,.22),
                inset 0 1px 0 rgba(255,255,255,.8);
            animation: assistantPanelIn .42s cubic-bezier(.18, .9, .24, 1) both;
        }

        .jarvis-panel::before {
            content: "";
            position: absolute;
            width: 120px;
            height: 120px;
            right: -45px;
            top: -45px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245,160,79,.18), transparent 68%);
            pointer-events: none;
        }

        .jarvis-panel::after {
            content: "";
            display: none;
        }

        .jarvis-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 118px minmax(0, 1fr);
            gap: 1rem;
            align-items: center;
        }

        .jarvis-orb {
            position: relative;
            width: 104px;
            height: 104px;
            border-radius: 28px;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 28% 20%, rgba(255,255,255,.96), transparent 18%),
                linear-gradient(145deg, #fff7ee, #ffd8b4);
            border: 2px solid rgba(224,112,32,.2);
            box-shadow:
                0 16px 34px rgba(224,112,32,.18),
                inset 0 1px 0 rgba(255,255,255,.9);
            animation: assistantFloat 3s ease-in-out infinite;
        }

        .jarvis-orb::before {
            content: "";
            position: absolute;
            left: 50%;
            top: -20px;
            width: 3px;
            height: 22px;
            border-radius: 999px;
            background: #e07020;
            transform: translateX(-50%);
        }

        .jarvis-orb::after {
            content: "";
            position: absolute;
            left: 50%;
            top: -30px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #f5a04f;
            transform: translateX(-50%);
            box-shadow: 0 0 18px rgba(245,160,79,.8);
        }

        .jarvis-orb .orb-ring {
            position: absolute;
            left: 17px;
            right: 17px;
            top: 25px;
            height: 36px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1f1f23, #3b2a20);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.14);
        }

        .jarvis-orb .orb-ring::before,
        .jarvis-orb .orb-ring::after {
            content: "";
            position: absolute;
            top: 13px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ffd8b4;
            box-shadow: 0 0 12px rgba(245,160,79,.9);
        }

        .jarvis-orb .orb-ring::before {
            left: 13px;
        }

        .jarvis-orb .orb-ring::after {
            right: 13px;
        }

        .jarvis-orb i {
            position: absolute;
            bottom: 22px;
            color: #8a3b0f;
            font-size: 1rem;
            z-index: 1;
        }

        .jarvis-orb.is-speaking .orb-ring::before,
        .jarvis-orb.is-speaking .orb-ring::after {
            animation: assistantBlink 3.4s infinite;
        }

        .jarvis-orb.is-speaking i {
            animation: assistantTalk .38s ease-in-out infinite;
        }

        .jarvis-hud-corners {
            display: none;
        }

        .jarvis-kicker {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            width: fit-content;
            margin-bottom: .55rem;
            padding: .36rem .68rem;
            border-radius: 999px;
            color: #8a3b0f;
            background: #fff1e5;
            border: 1px solid rgba(224,112,32,.16);
            font-size: .78rem;
            font-weight: 900;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .jarvis-title {
            margin: 0;
            font-size: clamp(1.65rem, 4vw, 2.55rem);
            font-weight: 950;
            letter-spacing: -.04em;
        }

        .jarvis-title span {
            color: #f5a04f;
        }

        .jarvis-line {
            min-height: 0;
            margin: .7rem 0 0;
            color: #6f5948;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.65;
            border-left: 3px solid rgba(224,112,32,.34);
            padding-left: 1rem;
        }

        .jarvis-weather-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            padding: .85rem .95rem;
            border-radius: 18px;
            background:
                radial-gradient(circle at top right, rgba(245,160,79,.16), transparent 34%),
                #fffaf4;
            border: 1px solid rgba(224,112,32,.16);
        }

        .jarvis-weather-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            color: #2b201a;
            background: linear-gradient(135deg, #fff2de, #f5a04f);
            box-shadow: 0 14px 26px rgba(245,160,79,.22);
            animation: assistantIconFloat 2.6s ease-in-out infinite;
        }

        .jarvis-weather-text strong {
            display: block;
            color: #2f2925;
            font-size: 1rem;
        }

        .jarvis-weather-text span {
            display: block;
            margin-top: .15rem;
            color: #7b6654;
            font-size: .92rem;
            font-weight: 650;
        }

        .jarvis-actions {
            display: flex;
            gap: .75rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .jarvis-btn {
            border: none;
            border-radius: 14px;
            padding: .72rem .95rem;
            color: #fff;
            background: linear-gradient(135deg, #e07020, #f5a04f);
            font-weight: 900;
            box-shadow: 0 14px 26px rgba(224,112,32,.25);
            transition: transform .22s ease, box-shadow .22s ease;
        }

        .jarvis-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 34px rgba(224,112,32,.34);
        }

        .jarvis-btn.is-muted {
            color: #8a3b0f;
            background: #fff1e5;
            border: 1px solid rgba(224,112,32,.18);
            box-shadow: none;
        }

        .jarvis-close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            z-index: 2;
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 50%;
            color: #8a3b0f;
            background: #fff1e5;
            transition: transform .22s ease, background .22s ease;
        }

        .jarvis-close:hover {
            transform: rotate(90deg);
            background: #ffe7d1;
        }

        @keyframes assistantPanelIn {
            from { opacity: 0; transform: translateY(22px) scale(.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes assistantFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @keyframes assistantTalk {
            0%, 100% { transform: translateY(0) scaleX(1); }
            50% { transform: translateY(2px) scaleX(1.22); }
        }

        @keyframes assistantIconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        @keyframes assistantBlink {
            0%, 46%, 50%, 100% { transform: scaleY(1); }
            48% { transform: scaleY(.15); }
        }

        .jarvis-welcome-overlay {
            color: #3b2a20;
            background:
                radial-gradient(circle at 50% 42%, rgba(245,160,79,.12), transparent 32%),
                rgba(255,255,255,.82);
            backdrop-filter: blur(12px);
        }

        .jarvis-panel {
            width: min(760px, 100%);
            padding: 0;
            border: none;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            overflow: visible;
        }

        .jarvis-panel::before,
        .jarvis-panel::after,
        .jarvis-hud-corners,
        .jarvis-kicker,
        .jarvis-title,
        .jarvis-weather-card {
            display: none !important;
        }

        .jarvis-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: min(620px, 82vh);
            gap: 2.2rem;
            text-align: center;
        }

        .jarvis-content > div:last-child {
            display: contents;
        }

        .jarvis-orb {
            width: min(340px, 58vw);
            height: min(340px, 58vw);
            border-radius: 50%;
            z-index: 2;
            border: 1px solid rgba(224,112,32,.18);
            background:
                radial-gradient(circle at 34% 28%, rgba(255,255,255,.96) 0 20%, rgba(255,255,255,.62) 21% 34%, transparent 35%),
                radial-gradient(circle at 72% 22%, rgba(255,194,90,.78), transparent 32%),
                radial-gradient(circle at 55% 88%, rgba(255,116,126,.42), transparent 30%),
                radial-gradient(circle at 88% 52%, rgba(255,210,74,.64), transparent 28%),
                linear-gradient(145deg, rgba(255,255,255,.88), rgba(255,238,231,.74));
            box-shadow:
                0 38px 95px rgba(224,112,32,.22),
                inset 18px 20px 38px rgba(255,255,255,.72),
                inset -16px -22px 42px rgba(245,160,79,.18),
                0 0 0 10px rgba(245,160,79,.045);
            filter: saturate(1.08);
            animation: liquidOrbIdle 5.5s ease-in-out infinite;
            transition: transform .16s ease, border-radius .18s ease, filter .18s ease;
        }

        .jarvis-orb::before {
            left: 7%;
            top: 5%;
            width: 86%;
            height: 86%;
            border-radius: 48% 52% 46% 54%;
            background:
                radial-gradient(circle at 58% 4%, rgba(255,103,119,.34), transparent 28%),
                radial-gradient(circle at 96% 50%, rgba(255,188,44,.6), transparent 26%),
                linear-gradient(135deg, transparent, rgba(255,255,255,.18));
            transform: none;
            animation: liquidOrbSwirl 7s ease-in-out infinite;
        }

        .jarvis-orb::after {
            left: 11%;
            top: 10%;
            width: 76%;
            height: 76%;
            border-radius: 55% 45% 52% 48%;
            background:
                radial-gradient(circle at 42% 8%, rgba(255,255,255,.42), transparent 22%),
                radial-gradient(circle at 80% 18%, rgba(255,196,80,.28), transparent 24%),
                transparent;
            transform: none;
            box-shadow: none;
            animation: liquidOrbSwirlReverse 8.5s ease-in-out infinite;
        }

        .jarvis-orb .orb-ring,
        .jarvis-orb i {
            display: none;
        }

        .jarvis-orb.is-speaking {
            animation: liquidOrbTalk .68s ease-in-out infinite;
            filter: saturate(1.18) brightness(1.03);
        }

        .jarvis-orb.is-word-pulse {
            transform: scale(1.075);
            border-radius: 47% 53% 51% 49%;
        }

        .jarvis-weather-stage {
            position: absolute;
            z-index: 0;
            top: 4%;
            left: 50%;
            width: min(560px, 92vw);
            height: min(380px, 58vh);
            transform: translateX(-50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity .45s ease, transform .55s ease;
        }

        .jarvis-weather-stage.is-active {
            opacity: 1;
            transform: translateX(-50%) translateY(-6px);
        }

        .jarvis-weather-stage.is-exiting {
            opacity: 0;
            transform: translateX(-50%) translateY(-22px) scale(.96);
        }

        .jarvis-weather-stage.is-exiting .weather-sun,
        .jarvis-weather-stage.is-exiting .weather-cloud,
        .jarvis-weather-stage.is-exiting .weather-rain,
        .jarvis-weather-stage.is-exiting .weather-snow,
        .jarvis-weather-stage.is-exiting .weather-fog,
        .jarvis-weather-stage.is-exiting .weather-lightning {
            animation-duration: .45s !important;
            filter: blur(8px);
        }

        .weather-sun,
        .weather-cloud,
        .weather-rain,
        .weather-snow,
        .weather-fog,
        .weather-lightning {
            position: absolute;
            opacity: 0;
        }

        .weather-sun {
            left: 50%;
            top: 16%;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background:
                radial-gradient(circle, #fff8df 0 28%, #ffd166 29% 52%, rgba(245,160,79,.36) 53% 100%);
            box-shadow:
                0 0 48px rgba(255,195,67,.68),
                0 0 120px rgba(245,160,79,.32);
            transform: translateX(-50%) scale(.4);
        }

        .weather-sun::before {
            content: "";
            position: absolute;
            inset: -42px;
            border-radius: inherit;
            background: conic-gradient(from 0deg, rgba(255,194,71,.0), rgba(255,194,71,.42), rgba(255,194,71,.0) 16%);
            animation: weatherSunRays 5s linear infinite, weatherSunBreath 1.6s ease-in-out infinite;
        }

        .weather-cloud {
            width: 178px;
            height: 62px;
            border-radius: 999px;
            background: rgba(255,255,255,.82);
            box-shadow:
                0 18px 35px rgba(94,75,63,.12),
                inset 0 1px 0 rgba(255,255,255,.9);
            filter: blur(.2px);
        }

        .weather-cloud::before,
        .weather-cloud::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: inherit;
        }

        .weather-cloud::before {
            width: 82px;
            height: 82px;
            left: 26px;
            top: -38px;
        }

        .weather-cloud::after {
            width: 104px;
            height: 104px;
            right: 18px;
            top: -52px;
        }

        .cloud-left {
            left: 3%;
            top: 24%;
            transform: translateX(-180px);
        }

        .cloud-right {
            right: 2%;
            top: 34%;
            transform: translateX(190px) scale(.88);
        }

        .cloud-top {
            left: 50%;
            top: 12%;
            transform: translateX(-50%) translateY(-80px) scale(.72);
        }

        .weather-rain {
            left: 50%;
            top: 46%;
            width: 250px;
            height: 135px;
            transform: translateX(-50%);
        }

        .weather-rain span,
        .weather-snow span {
            position: absolute;
            display: block;
        }

        .weather-rain span {
            width: 4px;
            height: 34px;
            border-radius: 999px;
            background: linear-gradient(180deg, rgba(73,154,255,0), rgba(73,154,255,.8));
            animation: weatherRainDrop .7s linear infinite;
        }

        .weather-rain span:nth-child(1) { left: 8%; animation-delay: 0s; }
        .weather-rain span:nth-child(2) { left: 22%; animation-delay: .12s; }
        .weather-rain span:nth-child(3) { left: 38%; animation-delay: .24s; }
        .weather-rain span:nth-child(4) { left: 55%; animation-delay: .08s; }
        .weather-rain span:nth-child(5) { left: 72%; animation-delay: .18s; }
        .weather-rain span:nth-child(6) { left: 88%; animation-delay: .28s; }

        .weather-snow {
            left: 50%;
            top: 38%;
            width: 280px;
            height: 170px;
            transform: translateX(-50%);
        }

        .weather-snow span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255,255,255,.95);
            box-shadow: 0 0 14px rgba(255,255,255,.8);
            animation: weatherSnowFall 2.4s ease-in-out infinite;
        }

        .weather-snow span:nth-child(1) { left: 5%; animation-delay: 0s; }
        .weather-snow span:nth-child(2) { left: 20%; animation-delay: .35s; }
        .weather-snow span:nth-child(3) { left: 36%; animation-delay: .7s; }
        .weather-snow span:nth-child(4) { left: 52%; animation-delay: .15s; }
        .weather-snow span:nth-child(5) { left: 70%; animation-delay: .55s; }
        .weather-snow span:nth-child(6) { left: 88%; animation-delay: .95s; }

        .weather-fog {
            left: 50%;
            top: 32%;
            width: min(440px, 86vw);
            height: 170px;
            transform: translateX(-50%);
        }

        .weather-fog span {
            position: absolute;
            left: 0;
            right: 0;
            height: 18px;
            border-radius: 999px;
            background: linear-gradient(90deg, transparent, rgba(214,205,196,.76), transparent);
            filter: blur(1px);
            animation: weatherFogFlow 3.2s ease-in-out infinite;
        }

        .weather-fog span:nth-child(1) { top: 8%; animation-delay: 0s; }
        .weather-fog span:nth-child(2) { top: 34%; animation-delay: .35s; }
        .weather-fog span:nth-child(3) { top: 60%; animation-delay: .7s; }

        .weather-lightning {
            left: 58%;
            top: 36%;
            width: 42px;
            height: 92px;
            clip-path: polygon(42% 0, 100% 0, 58% 42%, 92% 42%, 26% 100%, 42% 56%, 0 56%);
            background: #ffd54f;
            filter: drop-shadow(0 0 16px rgba(255,213,79,.92));
            transform: scale(.8);
        }

        .weather-sunny .weather-sun,
        .weather-clear .weather-sun,
        .weather-calm .weather-sun {
            opacity: 1;
            animation: weatherSunPop .8s cubic-bezier(.2,1.2,.3,1) both;
        }

        .weather-partly .weather-sun {
            opacity: 1;
            animation: weatherSunPop .8s cubic-bezier(.2,1.2,.3,1) both;
        }

        .weather-partly .cloud-left,
        .weather-cloudy .weather-cloud,
        .weather-rainy .weather-cloud,
        .weather-showery .weather-cloud,
        .weather-snowy .weather-cloud,
        .weather-stormy .weather-cloud {
            opacity: 1;
        }

        .weather-partly .cloud-left {
            animation: weatherCloudLeft .95s cubic-bezier(.2,1,.3,1) both, weatherCloudDrift 4s ease-in-out .95s infinite;
        }

        .weather-cloudy .cloud-left,
        .weather-rainy .cloud-left,
        .weather-showery .cloud-left,
        .weather-snowy .cloud-left,
        .weather-stormy .cloud-left {
            animation: weatherCloudLeft .95s cubic-bezier(.2,1,.3,1) both, weatherCloudDrift 4s ease-in-out .95s infinite;
        }

        .weather-cloudy .cloud-right,
        .weather-rainy .cloud-right,
        .weather-showery .cloud-right,
        .weather-snowy .cloud-right,
        .weather-stormy .cloud-right {
            animation: weatherCloudRight .95s cubic-bezier(.2,1,.3,1) both, weatherCloudDriftAlt 4.4s ease-in-out .95s infinite;
        }

        .weather-cloudy .cloud-top,
        .weather-stormy .cloud-top {
            animation: weatherCloudTop .95s cubic-bezier(.2,1,.3,1) both, weatherCloudDrift 4.8s ease-in-out .95s infinite;
        }

        .weather-rainy .weather-rain,
        .weather-showery .weather-rain,
        .weather-stormy .weather-rain {
            opacity: 1;
        }

        .weather-showery .weather-rain {
            animation: weatherShowerPulse 1.35s ease-in-out infinite;
        }

        .weather-snowy .weather-snow,
        .weather-foggy .weather-fog {
            opacity: 1;
        }

        .weather-stormy .weather-lightning {
            opacity: 1;
            animation: weatherLightning 1.25s ease-in-out infinite;
        }

        @keyframes weatherSunPop {
            0% { transform: translateX(-50%) scale(.25); opacity: 0; }
            72% { transform: translateX(-50%) scale(1.08); opacity: 1; }
            100% { transform: translateX(-50%) scale(1); opacity: 1; }
        }

        @keyframes weatherSunRays {
            to { transform: rotate(360deg); }
        }

        @keyframes weatherSunBreath {
            0%, 100% { inset: -34px; opacity: .58; }
            50% { inset: -56px; opacity: .9; }
        }

        @keyframes weatherCloudLeft {
            to { transform: translateX(0); }
        }

        @keyframes weatherCloudRight {
            to { transform: translateX(0) scale(.88); }
        }

        @keyframes weatherCloudTop {
            to { transform: translateX(-50%) translateY(0) scale(.72); }
        }

        @keyframes weatherCloudDrift {
            0%, 100% { margin-top: 0; }
            50% { margin-top: -8px; }
        }

        @keyframes weatherCloudDriftAlt {
            0%, 100% { margin-top: 0; }
            50% { margin-top: 7px; }
        }

        @keyframes weatherRainDrop {
            from { transform: translateY(-20px) rotate(12deg); opacity: 0; }
            20% { opacity: 1; }
            to { transform: translateY(110px) rotate(12deg); opacity: 0; }
        }

        @keyframes weatherSnowFall {
            from { transform: translateY(-20px) translateX(0); opacity: 0; }
            20% { opacity: 1; }
            to { transform: translateY(130px) translateX(24px); opacity: 0; }
        }

        @keyframes weatherFogFlow {
            0%, 100% { transform: translateX(-24px); opacity: .35; }
            50% { transform: translateX(24px); opacity: .9; }
        }

        @keyframes weatherLightning {
            0%, 72%, 100% { opacity: 0; transform: scale(.72); }
            76%, 82% { opacity: 1; transform: scale(1); }
            86% { opacity: .25; }
            90% { opacity: 1; }
        }

        @keyframes weatherShowerPulse {
            0%, 100% { transform: translateX(-50%) scaleY(.88); opacity: .65; }
            50% { transform: translateX(-50%) scaleY(1.08); opacity: 1; }
        }

        .jarvis-line {
            width: min(720px, 92vw);
            min-height: 72px;
            margin: 0;
            padding: 0 1rem;
            border: none;
            color: #3b2a20;
            font-size: clamp(1.35rem, 3.2vw, 2.45rem);
            font-weight: 850;
            line-height: 1.35;
            letter-spacing: -.03em;
            text-wrap: balance;
            text-shadow: 0 12px 30px rgba(224,112,32,.12);
            transition: opacity .18s ease, transform .18s ease;
        }

        .jarvis-line.is-changing {
            opacity: 0;
            transform: translateY(10px);
        }

        .jarvis-actions {
            display: none;
            justify-content: center;
            gap: .85rem;
            margin-top: -1.1rem;
        }

        .jarvis-actions.is-visible {
            display: flex !important;
        }

        .jarvis-game-panel {
            display: none;
            width: min(430px, 92vw);
            margin-top: -1.1rem;
            padding: 1rem;
            border-radius: 22px;
            background: rgba(255,255,255,.74);
            border: 1px solid rgba(224,112,32,.16);
            box-shadow: 0 18px 42px rgba(54,38,26,.12);
        }

        .jarvis-game-panel.is-visible {
            display: block;
        }

        .word-game-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: .45rem;
            margin-bottom: .8rem;
        }

        .word-game-cell {
            display: grid;
            place-items: center;
            height: 46px;
            border-radius: 12px;
            background: #fffaf4;
            border: 1px solid rgba(224,112,32,.18);
            color: #3b2a20;
            font-weight: 950;
            text-transform: uppercase;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
        }

        .word-game-cell.correct {
            color: #fff;
            background: #2f8f5b;
            border-color: #2f8f5b;
        }

        .word-game-cell.present {
            color: #fff;
            background: #e0a02c;
            border-color: #e0a02c;
        }

        .word-game-cell.miss {
            color: #fff;
            background: #8a7b70;
            border-color: #8a7b70;
        }

        .word-game-form {
            display: flex;
            gap: .65rem;
        }

        .word-game-form input {
            flex: 1;
            min-width: 0;
            border: 1px solid rgba(224,112,32,.22);
            border-radius: 14px;
            padding: .78rem .9rem;
            background: #fffdf9;
            color: #3b2a20;
            font-weight: 850;
            letter-spacing: .12em;
            text-transform: uppercase;
            outline: none;
        }

        .word-game-form button {
            border: none;
            border-radius: 14px;
            padding: .78rem .95rem;
            color: #fff;
            background: linear-gradient(135deg, #e07020, #f5a04f);
            font-weight: 900;
        }

        .jarvis-close {
            right: 1.1rem;
            top: 1.1rem;
            background: rgba(255,255,255,.78);
            border: 1px solid rgba(224,112,32,.14);
            box-shadow: 0 14px 28px rgba(54,38,26,.12);
        }

        @keyframes liquidOrbIdle {
            0%, 100% {
                transform: translateY(0) scale(1);
                border-radius: 50% 50% 48% 52%;
            }
            35% {
                transform: translateY(-10px) scale(1.018);
                border-radius: 48% 52% 53% 47%;
            }
            70% {
                transform: translateY(6px) scale(.992);
                border-radius: 53% 47% 49% 51%;
            }
        }

        @keyframes liquidOrbTalk {
            0%, 100% {
                transform: scale(1);
                border-radius: 50% 50% 48% 52%;
            }
            35% {
                transform: scale(1.045) translateY(-3px);
                border-radius: 47% 53% 55% 45%;
            }
            70% {
                transform: scale(.985) translateY(2px);
                border-radius: 54% 46% 47% 53%;
            }
        }

        @keyframes liquidOrbSwirl {
            0%, 100% { transform: rotate(0deg) scale(1); opacity: .82; }
            50% { transform: rotate(10deg) scale(1.04); opacity: 1; }
        }

        @keyframes liquidOrbSwirlReverse {
            0%, 100% { transform: rotate(0deg) scale(1); opacity: .72; }
            50% { transform: rotate(-12deg) scale(1.03); opacity: .94; }
        }

        @media (max-width: 720px) {
            .jarvis-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .jarvis-orb {
                margin: 0 auto;
            }

            .jarvis-kicker,
            .jarvis-actions {
                margin-left: auto;
                margin-right: auto;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="skillbridge-front">
    <?php include __DIR__ . '/partials/front_navbar.php'; ?>

    <?php if ($showWelcomeAssistant): ?>
    <div class="jarvis-welcome-overlay" id="jarvisWelcomeOverlay" data-username="<?= htmlspecialchars($welcomeName, ENT_QUOTES) ?>">
        <div class="jarvis-panel" role="dialog" aria-modal="true" aria-labelledby="jarvisWelcomeTitle">
            <div class="jarvis-hud-corners" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
            <button type="button" class="jarvis-close" id="jarvisCloseBtn" aria-label="Close welcome assistant">
                <i class="fas fa-xmark"></i>
            </button>
            <div class="jarvis-content">
                <div class="jarvis-weather-stage" id="jarvisWeatherStage" aria-hidden="true">
                    <div class="weather-sun"></div>
                    <div class="weather-cloud cloud-left"></div>
                    <div class="weather-cloud cloud-right"></div>
                    <div class="weather-cloud cloud-top"></div>
                    <div class="weather-rain">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                    <div class="weather-snow">
                        <span></span><span></span><span></span><span></span><span></span><span></span>
                    </div>
                    <div class="weather-fog">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="weather-lightning"></div>
                </div>
                <div class="jarvis-orb" aria-hidden="true">
                    <span class="orb-ring"></span>
                    <i class="fas fa-microchip"></i>
                </div>
                <div>
                    <div class="jarvis-kicker"><i class="fas fa-satellite-dish"></i> SkillBridge assistant online</div>
                    <h1 class="jarvis-title" id="jarvisWelcomeTitle">Hello <span><?= htmlspecialchars($welcomeName) ?></span></h1>
                    <p class="jarvis-line" id="jarvisLine">Welcome back to SkillBridge.</p>
                    <div class="jarvis-weather-card">
                        <div class="jarvis-weather-icon" id="jarvisWeatherIcon"><i class="fas fa-location-crosshairs"></i></div>
                        <div class="jarvis-weather-text">
                            <strong id="jarvisWeatherTitle">Daily briefing</strong>
                            <span id="jarvisWeatherDetails">Your welcome summary will appear here.</span>
                        </div>
                    </div>
                    <div class="jarvis-actions">
                        <button type="button" class="jarvis-btn" id="jarvisStartBtn">
                            Play daily word
                        </button>
                        <button type="button" class="jarvis-btn is-muted" id="jarvisSkipBtn">Maybe later</button>
                    </div>
                    <div class="jarvis-game-panel" id="jarvisGamePanel">
                        <div id="wordGameBoard"></div>
                        <form class="word-game-form" id="wordGameForm">
                            <input type="text" id="wordGameInput" maxlength="5" placeholder="Guess" autocomplete="off">
                            <button type="submit">Try</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="front-page">
        <section class="hero-surface">
            <div class="hero-video-wrap">
                <video autoplay muted loop playsinline poster="<?= htmlspecialchars($heroVideoPoster) ?>">
                    <source src="<?= htmlspecialchars($heroVideoSrc) ?>" type="video/mp4">
                </video>
            </div>

            <div class="hero-content">
                <div class="hero-kicker">
                    <i class="fas fa-bolt"></i>
                    <span>Trusted talent, ready to build</span>
                </div>
                <h1 class="hero-title">Connect With <span>Top Talent</span></h1>
                <p class="hero-desc">SkillBridge relie les clients et les freelancers dans une interface moderne, claire et inspiree du style premium de votre autre projet.</p>
                <div class="hero-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="?action=profile" class="sb-btn-outline"><i class="fas fa-user"></i> Mon profil</a>
                    <?php else: ?>
                        <a href="?action=login" class="sb-btn"><i class="fas fa-right-to-bracket"></i> Login</a>
                        <a href="?action=register" class="sb-btn-outline"><i class="fas fa-user-plus"></i> Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section-surface" id="categories">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Pourquoi <span>SkillBridge</span></h2>
                    <p class="section-subtitle">Une experience plus elegante et plus lisible pour presenter les talents, les services et l espace utilisateur.</p>
                </div>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-magnifying-glass"></i></div>
                    <h3>Recherche plus claire</h3>
                    <p>Trouvez rapidement les bons profils et les bons services avec un affichage plus propre et plus moderne.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-heart"></i></div>
                    <h3>Confiance & securite</h3>
                    <p>Les informations importantes sont mieux mises en avant pour rassurer les utilisateurs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-gauge-high"></i></div>
                    <h3>Navigation plus fluide</h3>
                    <p>Le style reprend un dashboard moderne avec cartes, surfaces et contrastes plus forts.</p>
                </div>
            </div>
        </section>

        <section class="section-surface">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Votre <span>Espace</span></h2>
                    <p class="section-subtitle">Les acces rapides changent selon votre role, tout en conservant la logique actuelle du projet.</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-user"></i></div>
                    <h3>Mon profil</h3>
                    <p>Consultez et mettez a jour vos informations personnelles.</p>
                    <a href="?action=profile" class="sb-btn-soft">Voir profil</a>
                </div>

                <?php if ($role == 2): ?>
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-user-group"></i></div>
                    <h3>Freelancers</h3>
                    <p>Parcourez les talents disponibles et choisissez le profil adapte a votre besoin.</p>
                    <a href="?action=freelancers" class="sb-btn-soft">Explorer</a>
                </div>
                <?php elseif ($role == 3): ?>
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-briefcase"></i></div>
                    <h3>Dashboard Freelancer</h3>
                    <p>Accedez a votre sidebar freelancer pour gerer votre CRUD services de maniere complete.</p>
                    <a href="?action=my_services" class="sb-btn-soft">Ouvrir mes services</a>
                </div>
                <?php endif; ?>

                <?php if ($role == 1): ?>
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Dashboard Admin</h3>
                    <p>Accedez a la zone d administration avec un habillage proche de project - Copy.</p>
                    <a href="?action=statistics" class="sb-btn-soft">Ouvrir</a>
                </div>
                <?php endif; ?>

                <?php if (!$isLoggedIn): ?>
                <div class="dashboard-card">
                    <div class="dashboard-icon"><i class="fas fa-rocket"></i></div>
                    <h3>Creer un compte</h3>
                    <p>Inscrivez-vous pour acceder a votre espace utilisateur, votre profil et aux fonctionnalites de la plateforme.</p>
                    <a href="?action=register" class="sb-btn-soft">S inscrire</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <?php if ($showWelcomeAssistant): ?>
    <script>
        (function() {
            const overlay = document.getElementById('jarvisWelcomeOverlay');
            if (!overlay) return;

            const userName = overlay.dataset.username || 'there';
            const line = document.getElementById('jarvisLine');
            const assistantOrb = overlay.querySelector('.jarvis-orb');
            const weatherStage = document.getElementById('jarvisWeatherStage');
            const weatherIcon = document.getElementById('jarvisWeatherIcon');
            const weatherTitle = document.getElementById('jarvisWeatherTitle');
            const weatherDetails = document.getElementById('jarvisWeatherDetails');
            const startButton = document.getElementById('jarvisStartBtn');
            const skipButton = document.getElementById('jarvisSkipBtn');
            const closeButton = document.getElementById('jarvisCloseBtn');
            const actions = overlay.querySelector('.jarvis-actions');
            const gamePanel = document.getElementById('jarvisGamePanel');
            const gameBoard = document.getElementById('wordGameBoard');
            const gameForm = document.getElementById('wordGameForm');
            const gameInput = document.getElementById('wordGameInput');
            let briefingStarted = false;
            let subtitleTimer = null;
            let pulseTimer = null;
            let gameStarted = false;
            let gameAttempts = 0;
            const dailyWords = ['BRAVE', 'FOCUS', 'SMART', 'BUILD', 'TRUST', 'LIGHT', 'SKILL'];
            const dailyWord = dailyWords[new Date().getDate() % dailyWords.length];

            const weatherMap = {
                0: ['sunny', 'fa-sun', 'sunny'],
                1: ['mostly clear', 'fa-cloud-sun', 'clear'],
                2: ['partly cloudy', 'fa-cloud-sun', 'partly'],
                3: ['cloudy', 'fa-cloud', 'cloudy'],
                45: ['foggy', 'fa-smog', 'foggy'],
                48: ['foggy', 'fa-smog', 'foggy'],
                51: ['lightly drizzly', 'fa-cloud-rain', 'rainy'],
                53: ['drizzly', 'fa-cloud-rain', 'rainy'],
                55: ['very drizzly', 'fa-cloud-rain', 'rainy'],
                56: ['freezing drizzly', 'fa-cloud-rain', 'rainy'],
                57: ['freezing drizzly', 'fa-cloud-rain', 'rainy'],
                61: ['rainy', 'fa-cloud-showers-heavy', 'rainy'],
                63: ['rainy', 'fa-cloud-showers-heavy', 'rainy'],
                65: ['very rainy', 'fa-cloud-showers-heavy', 'rainy'],
                66: ['freezing rainy', 'fa-cloud-showers-heavy', 'rainy'],
                67: ['freezing rainy', 'fa-cloud-showers-heavy', 'rainy'],
                71: ['snowy', 'fa-snowflake', 'snowy'],
                73: ['snowy', 'fa-snowflake', 'snowy'],
                75: ['very snowy', 'fa-snowflake', 'snowy'],
                77: ['snowy', 'fa-snowflake', 'snowy'],
                80: ['showery', 'fa-cloud-sun-rain', 'showery'],
                81: ['showery', 'fa-cloud-sun-rain', 'showery'],
                82: ['heavily showery', 'fa-cloud-sun-rain', 'showery'],
                85: ['snow showery', 'fa-snowflake', 'snowy'],
                86: ['heavy snow showery', 'fa-snowflake', 'snowy'],
                95: ['stormy', 'fa-cloud-bolt', 'stormy'],
                96: ['stormy with hail', 'fa-cloud-bolt', 'stormy'],
                99: ['stormy with heavy hail', 'fa-cloud-bolt', 'stormy']
            };

            function setLine(text) {
                clearTimeout(subtitleTimer);
                line.classList.add('is-changing');
                subtitleTimer = setTimeout(function() {
                    line.textContent = text;
                    line.classList.remove('is-changing');
                }, 170);
            }

            function closeAssistant() {
                window.speechSynthesis && window.speechSynthesis.cancel();
                clearTimeout(pulseTimer);
                if (assistantOrb) assistantOrb.classList.remove('is-speaking', 'is-word-pulse');
                resetWeatherStage();
                overlay.classList.remove('is-visible');
                setTimeout(function() {
                    overlay.remove();
                }, 260);
            }

            function resetWeatherStage() {
                if (!weatherStage) return;
                weatherStage.className = 'jarvis-weather-stage';
            }

            function showWeatherStage(type) {
                if (!weatherStage) return;
                resetWeatherStage();
                weatherStage.offsetHeight;
                weatherStage.classList.add('is-active', 'weather-' + (type || 'calm'));
            }

            function hideWeatherStage(onDone) {
                if (!weatherStage || !weatherStage.classList.contains('is-active')) {
                    if (onDone) onDone();
                    return;
                }

                weatherStage.classList.add('is-exiting');
                setTimeout(function() {
                    resetWeatherStage();
                    if (onDone) onDone();
                }, 520);
            }

            function pickVoice() {
                const voices = window.speechSynthesis ? window.speechSynthesis.getVoices() : [];
                return voices.find(function(voice) {
                    return /Daniel|Google UK English Male|Microsoft David|Microsoft Mark|Alex/i.test(voice.name);
                }) || voices.find(function(voice) {
                    return /^en/i.test(voice.lang);
                }) || voices[0] || null;
            }

            function pulseOrb() {
                if (!assistantOrb) return;
                assistantOrb.classList.add('is-word-pulse');
                clearTimeout(pulseTimer);
                pulseTimer = setTimeout(function() {
                    assistantOrb.classList.remove('is-word-pulse');
                }, 130);
            }

            function speak(text, onEnd) {
                if (!('speechSynthesis' in window)) {
                    if (onEnd) setTimeout(onEnd, 2000);
                    return;
                }

                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                const voice = pickVoice();
                if (voice) utterance.voice = voice;
                utterance.rate = 0.94;
                utterance.pitch = 0.94;
                utterance.volume = 1;
                utterance.onstart = function() {
                    if (assistantOrb) assistantOrb.classList.add('is-speaking');
                };
                utterance.onboundary = function(event) {
                    if (event.name === 'word' || event.charIndex >= 0) {
                        pulseOrb();
                    }
                };
                utterance.onend = function() {
                    if (assistantOrb) assistantOrb.classList.remove('is-speaking', 'is-word-pulse');
                    if (onEnd) onEnd();
                };
                utterance.onerror = function() {
                    if (assistantOrb) assistantOrb.classList.remove('is-speaking', 'is-word-pulse');
                    if (onEnd) onEnd();
                };
                window.speechSynthesis.speak(utterance);
            }

            function speakSegments(segments, onEnd) {
                let index = 0;

                function nextSegment() {
                    if (index >= segments.length) {
                        if (onEnd) onEnd();
                        return;
                    }

                    const segmentData = segments[index++];
                    const segment = typeof segmentData === 'string' ? segmentData : segmentData.text;
                    const weatherType = typeof segmentData === 'string' ? null : segmentData.weatherType;
                    setLine(segment);

                    if (weatherType) {
                        showWeatherStage(weatherType);
                    }

                    speak(segment, function() {
                        if (weatherType) {
                            hideWeatherStage(function() {
                                setTimeout(nextSegment, 120);
                            });
                            return;
                        }

                        setTimeout(nextSegment, 170);
                    });
                }

                nextSegment();
            }

            function showGamePrompt() {
                const prompt = 'Quick question. Want to play today\'s word challenge?';
                startButton.disabled = false;
                startButton.textContent = 'Play daily word';
                setLine(prompt);
                actions.classList.add('is-visible');
                speak(prompt, null);
            }

            function renderGuess(guess) {
                const row = document.createElement('div');
                row.className = 'word-game-row';
                const remaining = dailyWord.split('');

                for (let index = 0; index < 5; index++) {
                    if (guess[index] === dailyWord[index]) {
                        remaining[index] = null;
                    }
                }

                for (let index = 0; index < 5; index++) {
                    const cell = document.createElement('div');
                    cell.className = 'word-game-cell';
                    cell.textContent = guess[index] || '';

                    if (guess[index] === dailyWord[index]) {
                        cell.classList.add('correct');
                    } else {
                        const foundIndex = remaining.indexOf(guess[index]);
                        if (foundIndex !== -1) {
                            cell.classList.add('present');
                            remaining[foundIndex] = null;
                        } else {
                            cell.classList.add('miss');
                        }
                    }

                    row.appendChild(cell);
                }

                gameBoard.appendChild(row);
            }

            function startWordGame() {
                if (gameStarted) return;
                gameStarted = true;
                actions.classList.remove('is-visible');
                gamePanel.classList.add('is-visible');
                setLine('Guess the five-letter word. Green means correct, gold means close.');
                speak('Nice. Guess the five letter word. Green means correct. Gold means close.', function() {
                    gameInput.focus();
                });
            }

            function handleGuess(event) {
                event.preventDefault();
                const guess = (gameInput.value || '').toUpperCase().replace(/[^A-Z]/g, '').slice(0, 5);
                if (guess.length !== 5) {
                    setLine('Type a five-letter word.');
                    speak('Type a five letter word.', null);
                    return;
                }

                gameInput.value = '';
                gameAttempts++;
                renderGuess(guess);

                if (guess === dailyWord) {
                    const winText = 'Perfect. You found today\'s word in ' + gameAttempts + ' tries.';
                    setLine(winText);
                    speak(winText, function() {
                        setTimeout(closeAssistant, 1400);
                    });
                    gameForm.querySelector('button').disabled = true;
                    gameInput.disabled = true;
                    return;
                }

                if (gameAttempts >= 6) {
                    const loseText = 'Good try. Today\'s word was ' + dailyWord + '.';
                    setLine(loseText);
                    speak(loseText, function() {
                        setTimeout(closeAssistant, 1600);
                    });
                    gameForm.querySelector('button').disabled = true;
                    gameInput.disabled = true;
                    return;
                }

                const nextText = 'Good try. You have ' + (6 - gameAttempts) + ' guesses left.';
                setLine(nextText);
                speak(nextText, function() {
                    gameInput.focus();
                });
            }

            function getPosition() {
                return new Promise(function(resolve, reject) {
                    if (!navigator.geolocation) {
                        reject(new Error('Geolocation is not supported in this browser.'));
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: false,
                        timeout: 9000,
                        maximumAge: 600000
                    });
                });
            }

            async function getWeatherBriefing() {
                const position = await getPosition();
                const latitude = position.coords.latitude.toFixed(4);
                const longitude = position.coords.longitude.toFixed(4);
                const url = 'https://api.open-meteo.com/v1/forecast?latitude=' + latitude
                    + '&longitude=' + longitude
                    + '&current=temperature_2m,weather_code'
                    + '&daily=temperature_2m_max,temperature_2m_min'
                    + '&timezone=auto';

                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Weather service is unavailable.');
                }

                const data = await response.json();
                const code = data.current && typeof data.current.weather_code !== 'undefined'
                    ? Number(data.current.weather_code)
                    : 3;
                const currentTemp = data.current && typeof data.current.temperature_2m !== 'undefined'
                    ? Math.round(Number(data.current.temperature_2m))
                    : null;
                const maxTemp = data.daily && data.daily.temperature_2m_max ? Number(data.daily.temperature_2m_max[0]) : null;
                const minTemp = data.daily && data.daily.temperature_2m_min ? Number(data.daily.temperature_2m_min[0]) : null;
                const averageTemp = maxTemp !== null && minTemp !== null
                    ? Math.round((maxTemp + minTemp) / 2)
                    : currentTemp;
                const weather = weatherMap[code] || ['calm', 'fa-cloud', 'calm'];

                weatherIcon.innerHTML = '<i class="fas ' + weather[1] + '"></i>';
                weatherTitle.textContent = 'Today is ' + weather[0];
                weatherDetails.textContent = 'Average temperature: ' + (averageTemp !== null ? averageTemp + ' degrees' : 'not available')
                    + (currentTemp !== null ? ' | Current: ' + currentTemp + ' degrees' : '');

                return {
                    condition: weather[0],
                    type: weather[2],
                    averageTemp: averageTemp,
                    currentTemp: currentTemp
                };
            }

            async function startBriefing() {
                if (briefingStarted) {
                    return;
                }
                briefingStarted = true;
                startButton.disabled = true;
                startButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Briefing...';

                let speechSegments = [];
                try {
                    const weather = await getWeatherBriefing();
                    const tempText = weather.averageTemp !== null ? weather.averageTemp + ' degrees' : 'an unavailable temperature';
                    speechSegments = [
                        'Hey ' + userName + ', welcome back to SkillBridge.',
                        'I checked the weather for your location.',
                        {
                            text: 'It looks ' + weather.condition + ' today, with an average temperature around ' + tempText + '.',
                            weatherType: weather.type
                        },
                        'Your workspace is ready. Have a productive day.'
                    ];
                } catch (error) {
                    weatherIcon.innerHTML = '<i class="fas fa-location-dot"></i>';
                    weatherTitle.textContent = 'Welcome briefing ready';
                    weatherDetails.textContent = 'Location was not available, so I prepared a quick workspace welcome.';
                    speechSegments = [
                        'Hey ' + userName + ', welcome back to SkillBridge.',
                        'Your workspace is ready.',
                        'I could not check the live weather this time, but you are all set.',
                        'Have a productive day.'
                    ];
                }

                overlay.classList.add('is-visible');
                speakSegments(speechSegments, function() {
                    setTimeout(showGamePrompt, 300);
                });
            }

            setTimeout(function() {
                startBriefing();
            }, 300);

            startButton.addEventListener('click', startWordGame);
            skipButton.addEventListener('click', closeAssistant);
            closeButton.addEventListener('click', closeAssistant);
            gameForm.addEventListener('submit', handleGuess);
        })();
    </script>
    <?php endif; ?>
</body>
</html>
