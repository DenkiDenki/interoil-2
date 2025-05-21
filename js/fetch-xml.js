async function fetchAndSendReports() {
    try {
      const response = await fetch("https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/");
      const xmlText = await response.text();
      const parser = new DOMParser();
      const xmlDoc = parser.parseFromString(xmlText, "application/xml");
  
      let reports = xmlDoc.getElementsByTagName("report");
  
      let newReports = [];
      for (let i = 0; i < reports.length; i++) {
        let headline = xmlDoc.getElementsByTagName("file_headline")[i];
        let headline1 = headline.textContent.trim();
        let locationNode = xmlDoc.getElementsByTagName("location")[i];
        let locationHref = locationNode.getAttribute("href");
        let publishedDate = xmlDoc.getElementsByTagName("published")[i];
        let dateAndTime = publishedDate.getAttribute("date");
        let date = dateAndTime.split("T")[0];
      
        newReports.push({
          title: headline1,
          link: locationHref,
          date: date,
        });

        //console.log(newReports[i]);
        
      }
  
      // Enviar a PHP por AJAX
      fetch(reports_object.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'guardar_reports',
          security: reports_object.nonce,
          datos: JSON.stringify(newReports)//reports_object
        })
      })
      .then(res => res.text());
      //.then(data => console.log('PHP respondió Reports:', data));
      
    } catch (error) {
      console.error("Error al obtener el XML de reports:", error);
    }
  }
  document.addEventListener('DOMContentLoaded', fetchAndSendReports);