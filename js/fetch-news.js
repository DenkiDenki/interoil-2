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
        let title = headline.textContent.trim();
        let locationNode = xmlDoc2.getElementsByTagName("location")[i];
        let link = locationNode.getAttribute("href");
        let publishedDate = xmlDoc2.getElementsByTagName("published")[i];
        let dateAndTime = publishedDate.getAttribute("date");
        let date = dateAndTime.split("T")[0];
      

        newReleases.push({
          title: title,
          link: link,
          date: date,
        });
       
        //console.log(newReleases[i]);
        
      }
          // Peticiones paralelas
      await Promise.all(newReleases.map(async (release, index) => {
        try {
          const res2 = await fetch(release.link);
          const htmlText2 = await res2.text();

          const parser = new DOMParser();
          const post = parser.parseFromString(htmlText2, "application/xml");

          const pageTitle = post.querySelector("headline")?.textContent.trim() || "Sin título";
          const postBody = post.querySelector("main")?.textContent.trim() || "Sin texto";

          newReleases[index].page_title = pageTitle;
          newReleases[index].post_body = postBody;
          
          console.log(`Texto de ${release.link}:`, postBody);
        } catch (err) {
          console.error(`Error al obtener contenido de ${release.link}:`, err);
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
      .then(res2 => res2.text());
      //.then(data => console.log('PHP respondió News:', data));
      
    } catch (error) {
      console.error("Error al obtener el XML News:", error);
    }
  }
  document.addEventListener('DOMContentLoaded', fetchAndSendNews);