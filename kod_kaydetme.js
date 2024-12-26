function runCode() {
    // HTML, CSS, JavaScript kodlarını al
    let htmlCode = document.querySelector('textarea[name="htmlCode"]').value;
    let cssCode = document.querySelector('textarea[name="cssCode"]').value;
    let jsCode = document.querySelector('textarea[name="jsCode"]').value;

    // Çıktıyı yazdıracağımız iframe'i al
    let outputIframe = document.getElementById("output");
    let outputDoc = outputIframe.contentDocument || outputIframe.contentWindow.document;

    // HTML ve CSS kodunu iframe'e ekle
    outputDoc.open();
    outputDoc.write(htmlCode + "<style>" + cssCode + "</style>");
    outputDoc.close();

    // Terminal kısmını temizle
    let terminal = document.getElementById("terminal");
    terminal.textContent = '';  // Terminali temizle

    // JavaScript kodunu çalıştır ve hataları terminalde göster
    try {
        new Function(jsCode)();  // JS kodunu çalıştır
    } catch (error) {
        terminal.textContent = "Hata: " + error.message;  // Hata mesajını terminalde göster
    }
}
