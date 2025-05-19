async function getAndSendNews() {
    try {
      const response = await fetch("https://rss.globenewswire.com/Hexmlreportfeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/");
      const xmlText = await response.text();
      const parser = new DOMParser();
      const xmlDoc = parser.parseFromString(xmlText, "application/xml");
  
      let releases = xmlDoc.getElementsByTagName("press_releases");
  
      let newReleases = [];
      for (let i = 0; i < releases.length; i++) {
        let headline = xmlDoc.getElementsByTagName("headline")[i];
        let headlineText = headline.textContent.trim();
        let locationNode = xmlDoc.getElementsByTagName("location")[i];
        let locationHref = locationNode.getAttribute("href");
        let publishedDate = xmlDoc.getElementsByTagName("published")[i];
        let dateAndTime = publishedDate.getAttribute("date");
        let date = dateAndTime.split("T")[0];
      
        newReleases.push({
          title: headlineText,
          link: locationHref,
          date: date,
        });

        console.log(newReleases[i]);
        
      }
          // Peticiones paralelas
      await Promise.all(newReports.map(async (report, index) => {
        try {
          const res = await fetch(report.link);
          const htmlText = await res.text();

          const parser = new DOMParser();
          const post = parser.parseFromString(htmlText, "application/xml");

          const pageTitle = post.querySelector("headline")?.textContent.trim() || "Sin título";
          const postBody = post.querySelector("main")?.textContent.trim() || "Sin texto";

          newReports[index].page_title = pageTitle;
          newReports[index].post_body = postBody;
          console.log(`Título de ${report.link}:`, pageTitle);
          console.log(`Texto de ${report.link}:`, postBody);
        } catch (err) {
          console.error(`Error al obtener contenido de ${report.link}:`, err);
          newReports[index].page_title = "Error al obtener título";
        }
      }));
  
      fetch(news_object.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'guardar_news',
          security: news_object.nonce,
          datos: JSON.stringify(newReleases)
        })
      })
      .then(res => res.text())
      .then(data => console.log('PHP respondió News:', data));
      
    } catch (error) {
      console.error("Error al obtener el XML News:", error);
    }
  }
  document.addEventListener('DOMContentLoaded', getAndSendNews);