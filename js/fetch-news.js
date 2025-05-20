async function fetchAndSendNews() {
    try {
      const response2 = await fetch("https://rss.globenewswire.com/HexmlFeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/");
      const xmlText2 = await response2.text();
      const parser2 = new DOMParser();
      const xmlDoc2 = parser2.parseFromString(xmlText2, "application/xml");
  
      let releases = xmlDoc2.getElementsByTagName("press_release");
      
      let newReleases = [];
      for (let i = 0; i < releases.length; i++) {
        let headline = xmlDoc2.getElementsByTagName("headline")[i];
        let headlineText = headline.textContent.trim();
        let locationNode = xmlDoc2.getElementsByTagName("location")[i];
        let locationHref = locationNode.getAttribute("href");
        let publishedDate = xmlDoc2.getElementsByTagName("published")[i];
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
      await Promise.all(newReleases.map(async (report, index) => {
        try {
          const res2 = await fetch(report.link);
          const htmlText2 = await res2.text();

          const parser = new DOMParser();
          const post = parser.parseFromString(htmlText2, "application/xml");

          const pageTitle = post.querySelector("headline")?.textContent.trim() || "Sin título";
          const postBody = post.querySelector("main")?.textContent.trim() || "Sin texto";

          newReleases[index].page_title = pageTitle;
          newReleases[index].post_body = postBody;
          console.log(`Título de ${report.link}:`, pageTitle);
          console.log(`Texto de ${report.link}:`, postBody);
        } catch (err) {
          console.error(`Error al obtener contenido de ${report.link}:`, err);
          newReleases[index].page_title = "Error al obtener título";
        }
      }));
  
      fetch(news_object.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'guardar_news',
          security: news_object.nonce,
          news: JSON.stringify(newReleases)
        })
      })
      .then(res2 => res2.text())
      .then(data => console.log('PHP respondió News:', data));
      
    } catch (error) {
      console.error("Error al obtener el XML News:", error);
    }
  }
  document.addEventListener('DOMContentLoaded', fetchAndSendNews);