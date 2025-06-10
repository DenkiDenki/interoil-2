function fetchAndSendNews() {
  fetch("https://rss.globenewswire.com/HexmlFeed/organization/dBwf4frPXJHvuGJ2iT_UgA==/")
    .then(response2 => response2.text())
    .then(xmlText2 => {
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
      }

      return Promise.all(
        newReleases.map((release, index) => {
          return fetch(release.link)
            .then(res2 => res2.text())
            .then(htmlText2 => {
              const parser = new DOMParser();
              const post = parser.parseFromString(htmlText2, "application/xml");

              const pageTitle = post.querySelector("headline")?.textContent.trim() || "No title";
              const postBody = post.querySelector("main")?.textContent.trim() || "No content";

              newReleases[index].page_title = pageTitle;
              newReleases[index].post_body = postBody;
            })
            .catch(err => {
              console.error(`Error getting content from ${release.link}:`, err);
              newReleases[index].page_title = "Error getting the títle";
            });
        })
      ).then(() => newReleases);
    })
    .then(newReleases => {
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
    })
    .catch(error => {
      console.error("Error getting the XML from News:", error);
    });
}

document.addEventListener('DOMContentLoaded', fetchAndSendNews);